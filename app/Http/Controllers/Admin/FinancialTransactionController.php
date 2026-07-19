<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FinancialTransactionState;
use App\Enums\FinancialTransactionType;
use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Services\FinancialTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FinancialTransactionController extends Controller
{
    public function __construct(private FinancialTransactionService $transactions) {}

    public function index(Request $request): View
    {
        $query = FinancialTransaction::query()->with(['order', 'orderReturn'])->latest('id');
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('admin.auth.financial-transactions.index', [
            'transactions' => $query->paginate(20)->withQueryString(),
            'types' => FinancialTransactionType::cases(),
            'statuses' => FinancialTransactionState::cases(),
        ]);
    }

    public function approve(FinancialTransaction $transaction): RedirectResponse
    {
        return $this->transition(fn () => $this->transactions->approve($transaction, auth('admin')->id()), 'Đã duyệt giao dịch.');
    }

    public function complete(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'bank_reference' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        $attachmentPath = $request->file('attachment')?->store('financial-documents', 'local');

        try {
            $this->transactions->complete(
                $transaction,
                auth('admin')->id(),
                $data['bank_reference'] ?? null,
                $attachmentPath ?: null,
            );

            return back()->with('success', 'Đã ghi nhận giao dịch hoàn tất.');
        } catch (\RuntimeException $exception) {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }

            return back()->with('error', $exception->getMessage());
        }
    }

    public function attachment(FinancialTransaction $transaction)
    {
        abort_if(! $transaction->attachment_path || ! Storage::disk('local')->exists($transaction->attachment_path), 404);

        return Storage::disk('local')->download($transaction->attachment_path);
    }

    public function fail(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        $data = $request->validate(['failure_reason' => ['required', 'string', 'max:2000']]);

        return $this->transition(
            fn () => $this->transactions->fail($transaction, auth('admin')->id(), $data['failure_reason']),
            'Đã ghi nhận giao dịch thất bại.',
        );
    }

    private function transition(callable $callback, string $message): RedirectResponse
    {
        try {
            $callback();

            return back()->with('success', $message);
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
