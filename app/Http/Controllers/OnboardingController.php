<?php
namespace App\Http\Controllers;

use App\Services\OnboardingService;

class OnboardingController
{
    public function show(): void
    {
        $service = new OnboardingService();
        view('register', [
            'roles' => $service->roles(),
            'steps' => $service->steps(),
            'errors' => [],
            'old' => [],
            'message' => $_GET['message'] ?? null,
        ]);
    }

    public function saveDraft(): void
    {
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }
        json_response((new OnboardingService())->saveDraft($_POST));
    }

    public function complete(): void
    {
        $service = new OnboardingService();
        if (!\verify_csrf()) {
            view('register', ['roles' => $service->roles(), 'steps' => $service->steps(), 'errors' => ['session' => 'Your secure session token expired. Please refresh gently.'], 'old' => $_POST, 'message' => null]);
            return;
        }

        $result = $service->complete($_POST, $_SERVER);
        if (!$result['ok']) {
            view('register', ['roles' => $service->roles(), 'steps' => $service->steps(), 'errors' => $result['errors'] ?? ['form' => $result['message']], 'old' => $_POST, 'message' => $result['message']]);
            return;
        }

        $_SESSION['user_email'] = $result['user']['email'];
        header('Location: /onboarding/success');
    }

    public function success(): void
    {
        $user = \current_user();
        if (!$user) {
            header('Location: /login');
            return;
        }
        view('onboarding_success', ['user' => $user]);
    }
}
