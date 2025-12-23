<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction; // 🔥 CHANGED from Transaction to AccountTransaction
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;
use Maatwebsite\Excel\Facades\Excel;

class AccountingReportController extends Controller
{
    /**
     * Display account statement
     */
    public function accountStatement(Request $request)
    {
        // Get all accounts for dropdown
        $accounts = Account::orderBy('name')->get();

        // If no account selected, just show the form
        if (!$request->has('account_id') || !$request->account_id) {
            return view('accounting.account-statement', compact('accounts'));
        }

        // Get selected account
        $selectedAccount = Account::findOrFail($request->account_id);

        // 🔥 FIXED: Use AccountTransaction instead of Transaction
        $query = AccountTransaction::where('account_id', $request->account_id);

        // Apply type filter
        if ($request->has('type') && $request->type != 'all') {
            $query->where('transaction_type', $request->type); // 🔥 FIXED: Changed 'type' to 'transaction_type'
        }

        // Handle date range based on quick_range or custom dates
        $startDate = null;
        $endDate = null;

        if ($request->has('quick_range') && $request->quick_range) {
            $dates = $this->getDateRangeFromQuickRange($request->quick_range);
            $startDate = $dates['start'];
            $endDate = $dates['end'];
        } else {
            // Use custom date range
            if ($request->has('start_date') && $request->start_date) {
                $startDate = $request->start_date;
            }
            if ($request->has('end_date') && $request->end_date) {
                $endDate = $request->end_date;
            }
        }

        // Apply date filters to query
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate); // 🔥 FIXED: Changed 'date' to 'transaction_date'
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate); // 🔥 FIXED: Changed 'date' to 'transaction_date'
        }

        // Get transactions ordered by date
        $transactions = $query->orderBy('transaction_date', 'asc') // 🔥 FIXED
                             ->orderBy('created_at', 'asc')
                             ->get();

        // Calculate opening balance (all transactions before start date)
        $openingBalance = $selectedAccount->initial_balance ?? 0;
        
        if ($startDate) {
            $previousTransactions = AccountTransaction::where('account_id', $request->account_id)
                ->whereDate('transaction_date', '<', $startDate) // 🔥 FIXED
                ->get();
            
            foreach ($previousTransactions as $trans) {
                if ($trans->transaction_type == 'credit') { // 🔥 FIXED
                    $openingBalance += $trans->amount;
                } else {
                    $openingBalance -= $trans->amount;
                }
            }
        }

        // Calculate totals
        $totalDebit = $transactions->where('transaction_type', 'debit')->sum('amount'); // 🔥 FIXED
        $totalCredit = $transactions->where('transaction_type', 'credit')->sum('amount'); // 🔥 FIXED

        // Handle export
        if ($request->has('export')) {
            return $this->exportStatement($request->export, [
                'selectedAccount' => $selectedAccount,
                'transactions' => $transactions,
                'openingBalance' => $openingBalance,
                'totalDebit' => $totalDebit,
                'totalCredit' => $totalCredit,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
        }

        return view('accounting.account-statement', compact(
            'accounts',
            'selectedAccount',
            'transactions',
            'openingBalance',
            'totalDebit',
            'totalCredit'
        ));
    }

    /**
     * Get date range from quick range option
     */
    private function getDateRangeFromQuickRange($quickRange)
    {
        $today = Carbon::today();
        
        switch ($quickRange) {
            case 'today':
                return [
                    'start' => $today->format('Y-m-d'),
                    'end' => $today->format('Y-m-d')
                ];
            
            case 'yesterday':
                $yesterday = Carbon::yesterday();
                return [
                    'start' => $yesterday->format('Y-m-d'),
                    'end' => $yesterday->format('Y-m-d')
                ];
            
            case 'last_7_days':
                return [
                    'start' => $today->copy()->subDays(7)->format('Y-m-d'),
                    'end' => $today->format('Y-m-d')
                ];
            
            case 'last_30_days':
                return [
                    'start' => $today->copy()->subDays(30)->format('Y-m-d'),
                    'end' => $today->format('Y-m-d')
                ];
            
            case 'last_90_days':
                return [
                    'start' => $today->copy()->subDays(90)->format('Y-m-d'),
                    'end' => $today->format('Y-m-d')
                ];
            
            case 'this_month':
                return [
                    'start' => $today->copy()->startOfMonth()->format('Y-m-d'),
                    'end' => $today->format('Y-m-d')
                ];
            
            case 'last_month':
                return [
                    'start' => $today->copy()->subMonth()->startOfMonth()->format('Y-m-d'),
                    'end' => $today->copy()->subMonth()->endOfMonth()->format('Y-m-d')
                ];
            
            case 'this_year':
                return [
                    'start' => $today->copy()->startOfYear()->format('Y-m-d'),
                    'end' => $today->format('Y-m-d')
                ];
            
            case 'last_year':
                return [
                    'start' => $today->copy()->subYear()->startOfYear()->format('Y-m-d'),
                    'end' => $today->copy()->subYear()->endOfYear()->format('Y-m-d')
                ];
            
            case 'all_time':
                return [
                    'start' => null,
                    'end' => null
                ];
            
            default:
                return [
                    'start' => null,
                    'end' => null
                ];
        }
    }

    /**
     * Export statement
     */
    private function exportStatement($format, $data)
    {
        if ($format == 'pdf') {
            return $this->exportPDF($data);
        } elseif ($format == 'excel') {
            return $this->exportExcel($data);
        }
    }

    /**
     * Export to PDF
     */
    private function exportPDF($data)
    {
        // Using DomPDF or similar
        $pdf = PDF::loadView('accounting.statement-pdf', $data);
        
        $filename = 'account-statement-' . $data['selectedAccount']->account_no . '-' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export to Excel
     */
    private function exportExcel($data)
    {
        $filename = 'account-statement-' . $data['selectedAccount']->account_no . '-' . date('Y-m-d') . '.xlsx';
        
        return $this->exportCSV($data);
    }

    /**
     * Export to CSV
     */
    private function exportCSV($data)
    {
        $filename = 'account-statement-' . $data['selectedAccount']->account_no . '-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Account info
            fputcsv($file, ['Account Statement']);
            fputcsv($file, ['Account Name', $data['selectedAccount']->name]);
            fputcsv($file, ['Account No', $data['selectedAccount']->account_no]);
            if ($data['startDate'] && $data['endDate']) {
                fputcsv($file, ['Period', $data['startDate'] . ' to ' . $data['endDate']]);
            }
            fputcsv($file, []);
            
            // Headers
            fputcsv($file, ['Date', 'Description', 'Reference', 'Debit', 'Credit', 'Balance']);
            
            // Opening balance
            $runningBalance = $data['openingBalance'];
            fputcsv($file, ['', 'Opening Balance', '', '', '', number_format($runningBalance, 2)]);
            
            // Transactions
            foreach ($data['transactions'] as $transaction) {
                if ($transaction->transaction_type == 'debit') {
                    $runningBalance -= $transaction->amount;
                    $debit = number_format($transaction->amount, 2);
                    $credit = '-';
                } else {
                    $runningBalance += $transaction->amount;
                    $debit = '-';
                    $credit = number_format($transaction->amount, 2);
                }
                
                fputcsv($file, [
                    date('d M Y', strtotime($transaction->transaction_date)),
                    $transaction->description,
                    $transaction->reference_type . '-' . $transaction->reference_id ?? '-',
                    $debit,
                    $credit,
                    number_format($runningBalance, 2)
                ]);
            }
            
            // Closing balance
            fputcsv($file, []);
            fputcsv($file, ['', 'Closing Balance', '', 
                number_format($data['totalDebit'], 2), 
                number_format($data['totalCredit'], 2), 
                number_format($runningBalance, 2)
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        return view('accounting.balance-sheet');
    }
}