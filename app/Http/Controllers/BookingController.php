<?php
namespace App\Http\Controllers;

use App\Policies\BookingPolicy;
use App\Services\SessionBookingService;

class BookingController
{
    public function index(): void
    {
        $user = \current_user();
        if (!$user || !BookingPolicy::book($user)) {
            http_response_code(403);
            view('errors/403', ['user' => $user]);
            return;
        }
        $service = new SessionBookingService();
        view('booking/index', ['user' => $user, 'booking' => $service->discovery($_GET), 'bookings' => $service->bookingsFor((int) $user['id'])]);
    }

    public function data(): void
    {
        $user = \current_user();
        if (!$user || !BookingPolicy::book($user)) {
            json_response(['ok' => false, 'message' => 'Booking permission is required.'], 403);
            return;
        }
        json_response(['ok' => true] + (new SessionBookingService())->discovery($_GET));
    }

    public function hold(): void
    {
        $this->guarded(fn ($user) => (new SessionBookingService())->createHold((int) $user['id'], $_POST));
    }

    public function confirm(): void
    {
        $this->guarded(fn ($user) => (new SessionBookingService())->confirm((int) $user['id'], (int) ($_POST['booking_hold_id'] ?? 0), $_POST));
    }

    public function reschedule(): void
    {
        $this->guarded(fn ($user) => (new SessionBookingService())->reschedule((int) $user['id'], (int) ($_POST['session_booking_id'] ?? 0), trim($_POST['starts_at'] ?? '')));
    }

    public function cancel(): void
    {
        $this->guarded(fn ($user) => (new SessionBookingService())->cancel((int) $user['id'], (int) ($_POST['session_booking_id'] ?? 0), trim($_POST['reason'] ?? 'Client requested cancellation.')));
    }

    private function guarded(callable $action): void
    {
        $user = \current_user();
        if (!$user || !BookingPolicy::book($user)) {
            json_response(['ok' => false, 'message' => 'Booking permission is required.'], 403);
            return;
        }
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }
        json_response($action($user));
    }
}
