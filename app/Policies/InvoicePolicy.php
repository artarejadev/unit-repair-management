<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Semua invoice hanya dikelola oleh ADMIN.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return false;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Invoice ISSUED boleh dibatalkan oleh ADMIN.
     * Invoice PAID tidak boleh dibatalkan.
     * Invoice CANCELED tidak bisa dibatalkan lagi.
     */
    public function cancel(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin()
            && $invoice->status === InvoiceStatus::ISSUED;
    }

    /**
     * Invoice ISSUED dapat ditandai sudah dibayar oleh ADMIN.
     */
    public function markAsPaid(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin()
            && $invoice->status === InvoiceStatus::ISSUED;
    }

    /**
     * Invoice PAID dapat ditandai sudah diambil oleh ADMIN.
     */
    public function markAsPickedUp(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin()
            && $invoice->status === InvoiceStatus::PAID
            && $invoice->picked_up_at === null;
    }
}