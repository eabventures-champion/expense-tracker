<?php

namespace App\Http\Controllers;

use App\Mail\UserAccountStatusMail;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\WorkspaceOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly WorkspaceOnboardingService $onboardingService
    ) {}

    /**
     * Display the SaaS User Approvals & Workspace Management console (Admin / Owner only).
     */
    public function index(): View
    {
        $user = $this->currentUser();
        abort_unless($user->isAdmin(), 403, 'Administrator access required.');

        $allUsers = User::withCount(['transactions', 'categories', 'paymentMethods'])
            ->orderByRaw("CASE WHEN approval_status = 'pending' THEN 0 WHEN approval_status = 'approved' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->get();

        $pendingCount = $allUsers->where('approval_status', 'pending')->count();
        $approvedCount = $allUsers->where('approval_status', 'approved')->count();

        return view('admin.users', compact('user', 'allUsers', 'pendingCount', 'approvedCount'));
    }

    /**
     * Approve a pending SaaS user account from the Admin Console, provision their sample-filled workspace,
     * and email the user that their account is ready.
     */
    public function approve(User $targetUser): RedirectResponse
    {
        $admin = $this->currentUser();
        abort_unless($admin->isAdmin(), 403, 'Administrator access required.');

        $this->performUserApproval($targetUser);

        return back()->with('success', "Approved {$targetUser->name} ({$targetUser->email}), provisioned their guided-tour workspace, and sent their welcome email.");
    }

    /**
     * Reject or revoke a user's SaaS approval status from the Admin Console and notify them via email.
     */
    public function reject(User $targetUser): RedirectResponse
    {
        $admin = $this->currentUser();
        abort_unless($admin->isAdmin(), 403, 'Administrator access required.');
        abort_if($targetUser->id === $admin->id, 422, 'You cannot revoke your own administrator account.');

        $this->performUserRejection($targetUser);

        return back()->with('success', "Updated {$targetUser->name} ({$targetUser->email}) status to rejected and sent notification email.");
    }

    /**
     * One-Click Signed Email Link: Approve user directly from the Admin's email inbox.
     */
    public function approveViaEmail(User $targetUser): View
    {
        $this->performUserApproval($targetUser);

        return view('emails.approval-action-result', [
            'status' => 'approved',
            'heading' => 'User Account Approved & Workspace Provisioned',
            'messageText' => "{$targetUser->name} ({$targetUser->email}) has been approved! Their private workspace has been populated with the guided-tour sample dataset, and a welcome email with a sign-in link has been dispatched to their inbox.",
        ]);
    }

    /**
     * One-Click Signed Email Link: Reject user directly from the Admin's email inbox.
     */
    public function rejectViaEmail(User $targetUser): View
    {
        abort_if($targetUser->isAdmin(), 422, 'Cannot reject an administrator account.');

        $this->performUserRejection($targetUser);

        return view('emails.approval-action-result', [
            'status' => 'rejected',
            'heading' => 'User Account Request Rejected',
            'messageText' => "The registration request for {$targetUser->name} ({$targetUser->email}) has been marked as rejected, and a status notification email has been sent.",
        ]);
    }

    /**
     * Shared approval & workspace provisioning logic + welcome email dispatch.
     */
    private function performUserApproval(User $targetUser): void
    {
        $targetUser->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'has_sample_data' => true,
            'tour_completed' => false,
        ]);

        if ($targetUser->transactions()->count() === 0) {
            $this->onboardingService->initializeUserWorkspace($targetUser, true);
        }

        // Automatically mark any unread 'new_user' notification for this user as read
        AppNotification::where('type', 'new_user')
            ->where('is_read', false)
            ->where('message', 'like', '%' . $targetUser->email . '%')
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        try {
            Mail::to($targetUser->email)->send(new UserAccountStatusMail($targetUser, 'approved'));
        } catch (\Throwable $e) {
            Log::warning('Failed to send user approval welcome email: ' . $e->getMessage());
        }
    }

    /**
     * Shared rejection logic + status email dispatch.
     */
    private function performUserRejection(User $targetUser): void
    {
        $targetUser->update([
            'approval_status' => 'rejected',
            'approved_at' => null,
        ]);

        // Automatically mark any unread 'new_user' notification for this user as read
        AppNotification::where('type', 'new_user')
            ->where('is_read', false)
            ->where('message', 'like', '%' . $targetUser->email . '%')
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        try {
            Mail::to($targetUser->email)->send(new UserAccountStatusMail($targetUser, 'rejected'));
        } catch (\Throwable $e) {
            Log::warning('Failed to send user rejection email: ' . $e->getMessage());
        }
    }

    /**
     * Visit a notification's target action URL and automatically mark it as read.
     */
    public function visitNotification(AppNotification $notification): RedirectResponse
    {
        $user = $this->currentUser();
        abort_unless((int) $notification->user_id === (int) $user->id, 403);

        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        $targetUrl = $notification->action_url ?: route('dashboard');

        return redirect($targetUrl);
    }

    /**
     * Toggle a single notification between Read and Unread status.
     */
    public function toggleNotificationRead(Request $request, AppNotification $notification): RedirectResponse
    {
        $user = $this->currentUser();
        abort_unless((int) $notification->user_id === (int) $user->id, 403);

        $markAsRead = !$notification->is_read;

        $notification->update([
            'is_read' => $markAsRead,
            'read_at' => $markAsRead ? now() : null,
        ]);

        return back()->with('open_notifications_tab', $markAsRead ? 'read' : 'unread');
    }

    /**
     * Mark all unread notifications for the current user/admin as read.
     */
    public function markAllNotificationsRead(): RedirectResponse
    {
        $user = $this->currentUser();

        AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()
            ->with('open_notifications_tab', 'read')
            ->with('success', 'All notifications have been marked as read.');
    }

    /**
     * Clear (delete) all notifications for the current user/admin.
     */
    public function clearAllNotifications(Request $request): RedirectResponse
    {
        $user = $this->currentUser();
        $scope = $request->input('scope', 'all'); // 'all' or 'read'

        $query = AppNotification::where('user_id', $user->id);
        if ($scope === 'read') {
            $query->where('is_read', true);
        }
        $query->delete();

        $request->session()->put('notifications_cleared_by_user_' . $user->id, true);

        return back()->with('success', $scope === 'read' ? 'All read notifications cleared.' : 'All notifications have been cleared.');
    }

    /**
     * Delete a single notification.
     */
    public function destroyNotification(AppNotification $notification): RedirectResponse
    {
        $user = $this->currentUser();
        abort_unless((int) $notification->user_id === (int) $user->id, 403);

        $wasRead = $notification->is_read;
        $notification->delete();

        return back()->with('open_notifications_tab', $wasRead ? 'read' : 'unread');
    }
}
