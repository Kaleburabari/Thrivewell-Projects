<?php
namespace App\Http\Controllers;

use App\Policies\VideoSessionPolicy;
use App\Services\VideoConsultationService;

class VideoConsultationController
{
    public function index(): void
    {
        $user = \current_user();
        if (!$user || !VideoSessionPolicy::access($user)) { http_response_code(403); view('errors/403', ['user' => $user]); return; }
        $service = new VideoConsultationService();
        $roomId = isset($_GET['room']) ? (int) $_GET['room'] : null;
        view('video/room', ['user' => $user, 'rooms' => $service->roomsFor((int) $user['id']), 'room' => $service->roomFor((int) $user['id'], $roomId)]);
    }

    public function data(): void
    {
        $user = \current_user();
        if (!$user || !VideoSessionPolicy::access($user)) { json_response(['ok' => false, 'message' => 'Video consultation access is required.'], 403); return; }
        $service = new VideoConsultationService();
        json_response(['ok' => true, 'rooms' => $service->roomsFor((int) $user['id']), 'room' => $service->roomFor((int) $user['id'], isset($_GET['room']) ? (int) $_GET['room'] : null)]);
    }

    public function deviceCheck(): void { $this->post(fn ($service, $user) => $service->recordDeviceCheck((int) $user['id'], (int) ($_POST['video_consultation_room_id'] ?? 0), $_POST)); }
    public function consent(): void { $this->post(fn ($service, $user) => $service->setRecordingConsent((int) $user['id'], (int) ($_POST['video_consultation_room_id'] ?? 0), isset($_POST['granted']))); }
    public function chat(): void { $this->post(fn ($service, $user) => $service->sendChat((int) $user['id'], (int) ($_POST['video_consultation_room_id'] ?? 0), (string) ($_POST['message'] ?? ''))); }
    public function notes(): void { $this->post(fn ($service, $user) => $service->saveNotes((int) $user['id'], (int) ($_POST['video_consultation_room_id'] ?? 0), (string) ($_POST['notes'] ?? ''), isset($_POST['shared_with_client']))); }
    public function aiSummary(): void { $this->post(fn ($service, $user) => $service->draftAiSummary((int) $user['id'], (int) ($_POST['video_consultation_room_id'] ?? 0), (string) ($_POST['summary'] ?? ''))); }

    private function post(callable $action): void
    {
        $user = \current_user();
        if (!$user || !VideoSessionPolicy::access($user)) { json_response(['ok' => false, 'message' => 'Video consultation access is required.'], 403); return; }
        if (!\verify_csrf()) { json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419); return; }
        json_response($action(new VideoConsultationService(), $user));
    }
}
