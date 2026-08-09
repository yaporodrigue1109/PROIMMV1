<?php

namespace App\Http\Controllers\Admin\Contact;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContactReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['new', 'in_progress', 'processed', 'closed'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $query = ContactMessage::query()->with('replies.admin')->latest();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($contactQuery) use ($search): void {
                $contactQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $contacts = $query->paginate(25)->withQueryString()->through(
            fn (ContactMessage $contact): array => [
                'id' => $contact->contact_message_id,
                'requestType' => $contact->request_type,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'subject' => $contact->subject,
                'message' => $contact->message,
                'status' => $contact->status,
                'createdAt' => $contact->created_at?->locale('fr')->translatedFormat('d M Y à H:i'),
                'processedAt' => $contact->processed_at?->locale('fr')->translatedFormat('d M Y à H:i'),
                'replies' => $contact->replies->map(fn (ContactReply $reply): array => [
                    'id' => $reply->contact_reply_id,
                    'admin' => $reply->admin?->name ?? 'Administrateur',
                    'recipient' => $reply->recipient,
                    'subject' => $reply->subject,
                    'message' => $reply->message,
                    'status' => $reply->status,
                    'sentAt' => $reply->sent_at?->locale('fr')->translatedFormat('d M Y à H:i'),
                    'createdAt' => $reply->created_at?->locale('fr')->translatedFormat('d M Y à H:i'),
                ])->values(),
            ],
        );

        $statsQuery = ContactMessage::query();

        return Inertia::render('Admin/Contacts/Index', [
            'contacts' => $contacts,
            'filters' => [
                'status' => $filters['status'] ?? '',
                'search' => $filters['search'] ?? '',
            ],
            'stats' => [
                'all' => (clone $statsQuery)->count(),
                'new' => (clone $statsQuery)->where('status', 'new')->count(),
                'inProgress' => (clone $statsQuery)->where('status', 'in_progress')->count(),
                'processed' => (clone $statsQuery)->where('status', 'processed')->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, ContactMessage $contact): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'in_progress', 'processed', 'closed'])],
        ]);

        $isProcessed = in_array($data['status'], ['processed', 'closed'], true);

        $contact->update([
            'status' => $data['status'],
            'processed_at' => $isProcessed ? ($contact->processed_at ?? now()) : null,
            'processed_by' => $isProcessed ? auth('admin')->id() : null,
        ]);

        return back()->with('success', 'Le statut de la demande a été mis à jour.');
    }

    public function reply(Request $request, ContactMessage $contact): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:3', 'max:10000'],
        ]);

        $reply = $contact->replies()->create([
            'admin_id' => auth('admin')->id(),
            'channel' => 'email',
            'recipient' => $contact->email,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'pending',
        ]);

        try {
            Mail::html($data['message'], function ($mail) use ($contact, $data): void {
                $mail->to($contact->email, $contact->name)->subject($data['subject']);
            });

            $isLogMailer = config('mail.default') === 'log';

            $reply->update([
                'status' => $isLogMailer ? 'logged' : 'sent',
                'sent_at' => $isLogMailer ? null : now(),
                'error_message' => null,
            ]);

            $contact->update([
                'status' => $isLogMailer ? 'in_progress' : 'processed',
                'processed_at' => $isLogMailer ? null : now(),
                'processed_by' => $isLogMailer ? null : auth('admin')->id(),
            ]);

            return $isLogMailer
                ? back()->with('error', 'La réponse est tracée, mais le serveur est en mode journal. Configurez SMTP pour l’envoyer réellement.')
                : back()->with('success', 'La réponse a été envoyée et enregistrée dans l’historique.');
        } catch (\Throwable $exception) {
            $reply->update([
                'status' => 'failed',
                'error_message' => str($exception->getMessage())->limit(2000)->toString(),
            ]);

            Log::error('Échec de réponse à une demande de contact', [
                'contact_message_id' => $contact->contact_message_id,
                'contact_reply_id' => $reply->contact_reply_id,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'La réponse a été enregistrée, mais son envoi par e-mail a échoué. Vérifiez la configuration SMTP.');
        }
    }
}
