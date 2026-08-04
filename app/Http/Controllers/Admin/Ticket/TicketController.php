<?php

namespace App\Http\Controllers\Admin\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Repositories\Agence\Interfaces\SupportTicketRepositoryInterface;

class TicketController extends Controller
{
    public function __construct(private SupportTicketRepositoryInterface $tickets) {}

    public function index(): Response
    {
        $tickets = $this->tickets->paginateForAdmin()->getCollection()->map(fn ($ticket) => [
            'id' => $ticket->reference, 'uuid' => $ticket->support_ticket_id, 'subject' => $ticket->sujet,
            'status' => $ticket->statut, 'priority' => $ticket->priorite, 'category' => $ticket->categorie,
            'updatedAt' => $ticket->updated_at?->diffForHumans(),
            'requester' => [
                'name' => $ticket->demandeur?->name ?? 'Utilisateur inconnu',
                'email' => $ticket->demandeur?->email ?? null,
                'agency' => $ticket->agence?->name ?? $ticket->agence?->code_agence ?? 'Agence inconnue',
            ],
            'messages' => collect([['id' => 'description', 'author' => $ticket->demandeur?->name ?? 'Agence', 'role' => 'client', 'at' => $ticket->created_at?->diffForHumans(), 'body' => $ticket->description]])->concat($ticket->messages->map(fn ($message) => ['id' => $message->support_message_id, 'author' => $message->auteur_type === 'agence' ? ($ticket->demandeur?->name ?? 'Agence') : 'Support Pros Immobilier', 'role' => $message->auteur_type === 'agence' ? 'client' : 'agent', 'at' => $message->created_at?->diffForHumans(), 'body' => $message->contenu]))->values(),
        ])->values();
        return Inertia::render('Admin/Tickets/Index', ['tickets' => $tickets, 'stats' => $this->tickets->globalStatistics()]);
    }

    public function reply(Request $request, string $ticket) { $model = $this->tickets->find($ticket); abort_unless($model, 404); $data = $request->validate(['message' => 'required|string|max:2000']); $model->messages()->create(['auteur_id' => auth('admin')->id(), 'auteur_type' => 'support', 'contenu' => $data['message']]); $this->tickets->updateStatus($model, 'pending'); return back(); }
    public function updateStatus(Request $request, string $ticket) { $model = $this->tickets->find($ticket); abort_unless($model, 404); $data = $request->validate(['status' => 'required|in:open,pending,resolved,closed']); $this->tickets->updateStatus($model, $data['status']); return back(); }
}
