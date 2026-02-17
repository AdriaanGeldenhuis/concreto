<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $messages = $query->paginate(20);
        return view('admin.messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage)
    {
        // Mark as read
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        return view('admin.messages.show', compact('contactMessage'));
    }

    public function reply(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:5000',
            'send_email' => 'nullable|boolean',
        ]);

        $contactMessage->update([
            'status' => 'replied',
            'admin_notes' => $request->admin_notes,
        ]);

        // Actually send email reply if checkbox is checked
        if ($request->boolean('send_email') && $contactMessage->email) {
            try {
                Mail::send('emails.message-reply', [
                    'contactMessage' => $contactMessage,
                    'replyText' => $request->admin_notes,
                ], function ($message) use ($contactMessage) {
                    $subject = $contactMessage->type === 'quote_request'
                        ? 'Re: Your Quote Request - Concreto'
                        : 'Re: Your Message - Concreto';

                    $message->to($contactMessage->email, $contactMessage->name)
                        ->subject($subject);
                });

                AuditLog::log('email_reply_sent', 'ContactMessage', $contactMessage->id, [
                    'email' => $contactMessage->email,
                ]);

                return redirect()->route('admin.messages.show', $contactMessage)
                    ->with('success', 'Reply emailed to ' . $contactMessage->email . ' and notes saved.');
            } catch (\Exception $e) {
                return redirect()->route('admin.messages.show', $contactMessage)
                    ->with('error', 'Notes saved but email failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.messages.show', $contactMessage)
            ->with('success', 'Notes saved and marked as replied.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        return redirect()->route('admin.messages.index')
            ->with('success', 'Message deleted.');
    }
}
