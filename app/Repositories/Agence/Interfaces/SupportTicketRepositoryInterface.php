<?php
namespace App\Repositories\Agence\Interfaces;
use App\Models\SupportTicket; use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface SupportTicketRepositoryInterface { public function paginate(string $agenceId, array $filters = []): LengthAwarePaginator; public function paginateForAdmin(array $filters = []): LengthAwarePaginator; public function findForAgence(string $id, string $agenceId): ?SupportTicket; public function find(string $id): ?SupportTicket; public function create(array $data): SupportTicket; public function addMessage(SupportTicket $ticket, array $data): SupportTicket; public function updateStatus(SupportTicket $ticket, string $status): SupportTicket; public function statistics(string $agenceId): array; public function globalStatistics(): array; }
