<?php
namespace App\Services;

class CredentialVerificationService
{
    public function vaultFor(int $userId): array
    {
        return [
            'documents' => Database::table('SELECT * FROM credential_documents WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC', [$userId]),
            'badges' => Database::table('SELECT * FROM verification_badges WHERE user_id = ? ORDER BY created_at DESC', [$userId]),
            'reviews' => Database::table('SELECT cr.*, cd.title FROM credential_reviews cr JOIN credential_documents cd ON cd.id = cr.credential_document_id WHERE cd.user_id = ? ORDER BY cr.created_at DESC', [$userId]),
            'summary' => $this->summaryFor($userId),
            'requirements' => $this->requirements(),
        ];
    }

    public function reviewQueue(): array
    {
        return Database::table(
            "SELECT cd.*, users.name as owner_name, users.email as owner_email, roles.label as role_label
             FROM credential_documents cd
             JOIN users ON users.id = cd.user_id
             JOIN roles ON roles.id = users.role_id
             WHERE cd.deleted_at IS NULL AND cd.status IN ('pending_review','revision_requested')
             ORDER BY cd.status = 'pending_review' DESC, cd.created_at ASC"
        );
    }

    public function submitMetadata(int $userId, array $input): array
    {
        $type = trim($input['document_type'] ?? '');
        $title = trim($input['title'] ?? '');
        $path = trim($input['storage_path'] ?? '');
        if (!$type || !$title || !$path) {
            return ['ok' => false, 'message' => 'Please provide document type, title, and private storage path metadata.'];
        }
        if (!str_starts_with($path, 'private/credentials/')) {
            return ['ok' => false, 'message' => 'Credential metadata must point to private credential storage.'];
        }

        $now = date('c');
        Database::execute(
            'INSERT INTO credential_documents(user_id,document_type,title,storage_disk,storage_path,file_hash,status,submitted_at,expires_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)',
            [$userId, $type, $title, 'private', $path, hash('sha256', $path.'|'.$userId), 'pending_review', $now, date('c', strtotime('+1 year')), $now, $now]
        );
        $document = Database::row('SELECT * FROM credential_documents WHERE user_id = ? ORDER BY id DESC LIMIT 1', [$userId]);
        AuditLogger::record($userId, 'credential_metadata_submitted', 'credential_document', (int) $document['id'], ['document_type' => $type, 'private_storage' => true]);

        return ['ok' => true, 'message' => 'Credential metadata submitted to the private vault.', 'document' => $document];
    }

    public function approve(int $reviewerId, int $documentId, string $notes): array
    {
        return $this->review($reviewerId, $documentId, 'verified', 'approved', $notes ?: 'Approved after private credential review.');
    }

    public function requestRevision(int $reviewerId, int $documentId, string $notes): array
    {
        return $this->review($reviewerId, $documentId, 'revision_requested', 'revision_requested', $notes ?: 'Please revise this credential metadata.');
    }

    public function signedDownloadToken(int $requesterId, int $documentId, string $purpose = 'credential_review'): array
    {
        $document = Database::row('SELECT * FROM credential_documents WHERE id = ? AND deleted_at IS NULL', [$documentId]);
        if (!$document) {
            return ['ok' => false, 'message' => 'Credential document was not found.'];
        }
        $now = date('c');
        $token = hash('sha256', $documentId.'|'.$requesterId.'|'.random_bytes(16));
        Database::execute(
            'INSERT INTO signed_download_tokens(user_id,credential_document_id,token,purpose,expires_at,created_at) VALUES(?,?,?,?,?,?)',
            [$requesterId, $documentId, $token, $purpose, date('c', strtotime('+15 minutes')), $now]
        );
        AuditLogger::record($requesterId, 'credential_signed_download_created', 'credential_document', $documentId, ['expires_in_minutes' => 15, 'purpose' => $purpose]);

        return ['ok' => true, 'message' => 'Signed credential download token created.', 'token' => $token, 'expires_at' => date('c', strtotime('+15 minutes'))];
    }

    public function requirements(): array
    {
        return [
            ['type' => 'license', 'label' => 'Professional license', 'required_for' => 'Counsellor'],
            ['type' => 'degree', 'label' => 'Degree or certificate', 'required_for' => 'Counsellor / Intern'],
            ['type' => 'government_id', 'label' => 'Government ID', 'required_for' => 'Counsellor / Intern'],
            ['type' => 'university_letter', 'label' => 'HOD or reference letter', 'required_for' => 'Intern'],
        ];
    }

    private function review(int $reviewerId, int $documentId, string $status, string $decision, string $notes): array
    {
        $document = Database::row('SELECT * FROM credential_documents WHERE id = ? AND deleted_at IS NULL', [$documentId]);
        if (!$document) {
            return ['ok' => false, 'message' => 'Credential document was not found.'];
        }

        $now = date('c');
        Database::execute(
            'UPDATE credential_documents SET status = ?, reviewer_id = ?, revision_reason = ?, reviewed_at = ?, updated_at = ? WHERE id = ?',
            [$status, $reviewerId, $decision === 'revision_requested' ? $notes : null, $now, $now, $documentId]
        );
        Database::execute(
            'INSERT INTO credential_reviews(credential_document_id,reviewer_id,decision,notes,created_at) VALUES(?,?,?,?,?)',
            [$documentId, $reviewerId, $decision, $notes, $now]
        );
        if ($status === 'verified') {
            Database::execute(
                'INSERT INTO verification_badges(user_id,badge,level,status,issued_at,expires_at,created_at) VALUES(?,?,?,?,?,?,?)',
                [(int) $document['user_id'], 'Verified Credential', $document['document_type'], 'active', $now, date('c', strtotime('+1 year')), $now]
            );
        }
        AuditLogger::record($reviewerId, 'credential_review_'.$decision, 'credential_document', $documentId, ['owner_id' => (int) $document['user_id'], 'status' => $status]);

        return ['ok' => true, 'message' => $status === 'verified' ? 'Credential verified and badge readiness updated.' : 'Revision request saved calmly.', 'document_id' => $documentId];
    }

    private function summaryFor(int $userId): array
    {
        $rows = Database::table('SELECT status, COUNT(*) as count FROM credential_documents WHERE user_id = ? AND deleted_at IS NULL GROUP BY status', [$userId]);
        $summary = ['verified' => 0, 'pending_review' => 0, 'revision_requested' => 0, 'total' => 0];
        foreach ($rows as $row) {
            $summary[$row['status']] = (int) $row['count'];
            $summary['total'] += (int) $row['count'];
        }
        return $summary;
    }
}
