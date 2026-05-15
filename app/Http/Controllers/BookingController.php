<?php
namespace App\Http\Controllers;

use App\Policies\BookingPolicy;
use App\Services\SessionBookingService;

class BookingController
{
    public function show(): void
    {
        $user = \current_user();
        if (!$user || !BookingPolicy::book($user)) {
            http_response_code(403);
            view('errors/403', ['user' => $user]);
            return;
        }
        $service = new SessionBookingService();
        view('booking/index', ['user' => $user, 'booking' => $service->discovery((int) $user['id']), 'bookings' => $service->bookingsFor((int) $user['id'])]);
    }

    public function data(): void
    {
        $user = \current_user();
        if (!$user || !BookingPolicy::book($user)) {
            json_response(['ok' => false, 'message' => 'Booking access is required.'], 403);
            return;
        }
        json_response(['ok' => true] + (new SessionBookingService())->discovery((int) $user['id'], $_GET));
    }

    public function hold(): void
    {
        $user = \current_user();
        if (!$user || !BookingPolicy::book($user)) {
            json_response(['ok' => false, 'message' => 'Booking access is required.'], 403);
            return;
        }
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }
        json_response((new SessionBookingService())->createHold((int) $user['id'], (int) ($_POST['availability_id'] ?? 0)));
    }

    public function confirm(): void
    {
        $user = \current_user();
        if (!$user || !BookingPolicy::book($user)) {
            json_response(['ok' => false, 'message' => 'Booking access is required.'], 403);
            return;
        }
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }
        json_response((new SessionBookingService())->confirm((int) $user['id'], (int) ($_POST['hold_id'] ?? 0), $_POST));
    }

    public function cancel(): void
    {
        $user = \current_user();
        if (!$user || !BookingPolicy::book($user)) {
            json_response(['ok' => false, 'message' => 'Booking access is required.'], 403);
            return;
        }
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }
        json_response((new SessionBookingService())->cancel((int) $user['id'], (int) ($_POST['session_booking_id'] ?? 0), trim($_POST['reason'] ?? 'Client requested a pause.')));
    }
}
