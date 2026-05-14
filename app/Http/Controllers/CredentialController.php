<?php
namespace App\Http\Controllers;

use App\Policies\CredentialPolicy;
use App\Services\CredentialVerificationService;

class CredentialController
{
    public function vault(): void
    {
        $user = \current_user();
        if (!$user || !CredentialPolicy::manage($user)) {
            http_response_code(403);
            view('errors/403', ['user' => $user]);
            return;
        }
        view('credentials/vault', ['user' => $user, 'vault' => (new CredentialVerificationService())->vaultFor((int) $user['id'])]);
    }

    public function submit(): void
    {
        $user = \current_user();
        if (!$user || !CredentialPolicy::manage($user)) {
            json_response(['ok' => false, 'message' => 'Credential vault permission is required.'], 403);
            return;
        }
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }
        json_response((new CredentialVerificationService())->submitMetadata((int) $user['id'], $_POST));
    }

    public function reviewQueue(): void
    {
        $user = \current_user();
        if (!$user || !CredentialPolicy::review($user)) {
            http_response_code(403);
            view('errors/403', ['user' => $user]);
            return;
        }
        view('credentials/review', ['user' => $user, 'queue' => (new CredentialVerificationService())->reviewQueue()]);
    }

    public function approve(): void
    {
        $this->reviewAction('approve');
    }

    public function requestRevision(): void
    {
        $this->reviewAction('revision');
    }

    public function signedDownload(): void
    {
        $user = \current_user();
        $documentId = (int) ($_POST['credential_document_id'] ?? 0);
        $document = \App\Services\Database::row('SELECT * FROM credential_documents WHERE id = ?', [$documentId]);
        if (!$user || !$document || !CredentialPolicy::signedDownload($user, $document)) {
            json_response(['ok' => false, 'message' => 'Signed credential access is not available for this account.'], 403);
            return;
        }
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }
        json_response((new CredentialVerificationService())->signedDownloadToken((int) $user['id'], $documentId, $_POST['purpose'] ?? 'credential_review'));
    }

    private function reviewAction(string $action): void
    {
        $user = \current_user();
        if (!$user || !CredentialPolicy::review($user)) {
            json_response(['ok' => false, 'message' => 'Credential review permission is required.'], 403);
            return;
        }
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }
        $service = new CredentialVerificationService();
        $documentId = (int) ($_POST['credential_document_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        json_response($action === 'approve' ? $service->approve((int) $user['id'], $documentId, $notes) : $service->requestRevision((int) $user['id'], $documentId, $notes));
    }
}
