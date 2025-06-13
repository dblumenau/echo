<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Get the raw transaction instance #4
$instanceId = 4;

// Start capturing HTML content
$htmlContent = '';
$htmlTransactions = [];

echo "=== Tracing Raw Transaction Instance #{$instanceId} ===\n\n";

// Get the instance details
$instance = DB::table('raw_transaction_instances')
    ->where('id', $instanceId)
    ->first();

echo "Instance Status: {$instance->status}\n";
echo "Instance Type: {$instance->type}\n";
echo "Created: {$instance->created_at}\n\n";

// Store instance data for HTML
$instanceData = [
    'id' => $instanceId,
    'status' => $instance->status,
    'type' => $instance->type,
    'created_at' => $instance->created_at
];

// Get all raw transactions for this instance
$rawTransactions = DB::table('raw_transactions')
    ->where('raw_transaction_instance_id', $instanceId)
    ->get();

echo "=== Raw Transactions ===\n";
foreach ($rawTransactions as $rt) {
    echo "\nRaw Transaction ID: {$rt->id}\n";
    echo "Status: {$rt->status}\n";
    if (isset($rt->external_id)) {
        echo "External ID: {$rt->external_id}\n";
    }
    echo "Created: {$rt->created_at}\n";
    
    // Initialize HTML transaction data
    $htmlTransaction = [
        'id' => $rt->id,
        'status' => $rt->status,
        'external_id' => $rt->external_id ?? null,
        'created_at' => $rt->created_at,
        'payload' => [],
        'journal_entry' => null,
        'postings' => [],
        'purchase_amounts' => [],
        'cashback_amounts' => [],
        'points_entry' => null,
        'member_aggregate' => null,
        'member_financial_aggregates' => []
    ];
    
    // Show the payload data
    if (isset($rt->payload)) {
        $payload = json_decode($rt->payload, true);
        if ($payload) {
            echo "Payload Data:\n";
            echo "  - Amount: " . ($payload['amount'] ?? 'N/A') . "\n";
            echo "  - Currency: " . ($payload['currency'] ?? 'N/A') . "\n";
            echo "  - External ID: " . ($payload['external_id'] ?? 'N/A') . "\n";
            if (isset($payload['member_id'])) echo "  - Member ID: {$payload['member_id']}\n";
            if (isset($payload['merchant_id'])) echo "  - Merchant ID: {$payload['merchant_id']}\n";
            
            // Show additional fields
            if (isset($payload['labels']) && !empty($payload['labels'])) {
                echo "  - Labels: " . implode(', ', $payload['labels']) . "\n";
            }
            if (isset($payload['logging'])) {
                echo "  - Logging: " . json_encode($payload['logging']) . "\n";
            }
            if (isset($payload['extensions'])) {
                echo "  - Extensions: " . json_encode($payload['extensions']) . "\n";
            }
            
            $htmlTransaction['payload'] = $payload;
        }
    }
    
    // Check journal_entry_origins for the transaction created from this raw transaction
    $journalOrigin = DB::table('journal_entry_origins')
        ->where('raw_transaction_id', $rt->id)
        ->first();
    
    if ($journalOrigin) {
        // Get the journal entry (transaction)
        $journalEntry = DB::table('journal_entries')
            ->where('id', $journalOrigin->journal_entry_id)
            ->first();
            
        if ($journalEntry) {
            echo "\n  ✓ Created Journal Entry (Transaction) ID: {$journalEntry->id}\n";
            echo "    Type: {$journalEntry->type}\n";
            echo "    Loyalty Type: {$journalEntry->loyalty_type}\n";
            echo "    Member ID: {$journalEntry->member_id}\n";
            echo "    Card ID: {$journalEntry->card_id}\n";
            echo "    Merchant ID: {$journalEntry->merchant_id}\n";
            echo "    Chain ID: {$journalEntry->chain_id}\n";
            echo "    Transaction Date: {$journalEntry->original_transaction_date}\n";
            echo "    Created: {$journalEntry->created_at}\n";
            
            // Store journal entry data for HTML
            $htmlTransaction['journal_entry'] = [
                'id' => $journalEntry->id,
                'type' => $journalEntry->type,
                'loyalty_type' => $journalEntry->loyalty_type,
                'member_id' => $journalEntry->member_id,
                'card_id' => $journalEntry->card_id,
                'merchant_id' => $journalEntry->merchant_id,
                'chain_id' => $journalEntry->chain_id,
                'transaction_date' => $journalEntry->original_transaction_date,
                'created_at' => $journalEntry->created_at
            ];
            
            // Check for labels
            $labels = DB::table('journal_entry_label')
                ->join('labels', 'journal_entry_label.label_id', '=', 'labels.id')
                ->where('journal_entry_label.journal_entry_id', $journalEntry->id)
                ->pluck('labels.name')
                ->toArray();
            
            if (!empty($labels)) {
                echo "\n    Labels: " . implode(', ', $labels) . "\n";
                $htmlTransaction['journal_entry']['labels'] = $labels;
            }
            
            // Check for extensions
            $journalExt = DB::table('journal_entry_ext')
                ->where('journal_entry_id', $journalEntry->id)
                ->first();
            
            if ($journalExt) {
                echo "\n    Extensions:\n";
                if (isset($journalExt->status) && $journalExt->status) echo "      - Status: {$journalExt->status}\n";
                if (isset($journalExt->household_id) && $journalExt->household_id) echo "      - Household ID: {$journalExt->household_id}\n";
                if (isset($journalExt->description) && $journalExt->description) echo "      - Description: {$journalExt->description}\n";
                
                $htmlTransaction['journal_entry']['extensions'] = [
                    'status' => $journalExt->status ?? null,
                    'household_id' => $journalExt->household_id ?? null,
                    'description' => $journalExt->description ?? null
                ];
            }
            
            // Check for logging (entity log entry and origins)
            if ($journalOrigin->entity_log_entry_id) {
                $logEntry = DB::table('entity_log_entries')
                    ->where('id', $journalOrigin->entity_log_entry_id)
                    ->first();
                
                if ($logEntry) {
                    echo "\n    Audit Log:\n";
                    echo "      - Entity: {$logEntry->entity_name} (ID: {$logEntry->entity_id})\n";
                    echo "      - Action: {$logEntry->action}\n";
                    
                    // Get the origin details (this is where logging data is stored)
                    $logOrigin = DB::table('entity_log_entry_origins')
                        ->where('entity_log_entry_id', $logEntry->id)
                        ->first();
                    
                    if ($logOrigin) {
                        echo "      - Service: {$logOrigin->service}\n";
                        echo "      - Invoker Type: {$logOrigin->invoker_type}\n";
                        echo "      - Invoker ID: {$logOrigin->invoker_id}\n";
                        if (isset($logOrigin->ip_address)) {
                            echo "      - IP Address: {$logOrigin->ip_address}\n";
                        }
                    }
                    
                    $htmlTransaction['journal_entry']['audit_log'] = [
                        'id' => $logEntry->id,
                        'entity_name' => $logEntry->entity_name,
                        'entity_id' => $logEntry->entity_id,
                        'action' => $logEntry->action,
                        'service' => $logOrigin->service ?? null,
                        'invoker_type' => $logOrigin->invoker_type ?? null,
                        'invoker_id' => $logOrigin->invoker_id ?? null,
                        'ip_address' => $logOrigin->ip_address ?? null
                    ];
                }
            }
            
            // Check postings (double-entry accounting)
            $postings = DB::table('postings')
                ->where('journal_entry_id', $journalEntry->id)
                ->get();
            
            if ($postings->count() > 0) {
                echo "\n    Postings (Double-Entry Accounting):\n";
                foreach ($postings as $posting) {
                    $account = DB::table('accounts')->where('id', $posting->account_id)->first();
                    $accountName = "ID: {$posting->account_id}";
                    if ($account) {
                        // Try different possible field names
                        if (isset($account->name)) {
                            $accountName = $account->name;
                        } elseif (isset($account->account_name)) {
                            $accountName = $account->account_name;
                        } elseif (isset($account->type)) {
                            $accountName = "Type: {$account->type}";
                        }
                    }
                    echo "      - Account: {$accountName}, ";
                    echo "Type: {$posting->type}, Amount: {$posting->amount}, Currency ID: {$posting->currency_id}\n";
                    
                    // Store posting data for HTML
                    $htmlTransaction['postings'][] = [
                        'account' => $accountName,
                        'type' => $posting->type,
                        'amount' => $posting->amount,
                        'currency_id' => $posting->currency_id
                    ];
                }
            }
            
            // Check purchase amounts
            $purchaseAmounts = DB::table('purchase_amounts')
                ->where('journal_entry_id', $journalEntry->id)
                ->get();
            
            if ($purchaseAmounts->count() > 0) {
                echo "\n    Purchase Amounts:\n";
                foreach ($purchaseAmounts as $pa) {
                    echo "      - Currency ID {$pa->currency_id}: {$pa->amount}";
                    $rate = null;
                    if ($pa->exchange_rate_id) {
                        $rateRecord = DB::table('exchange_rates')->where('id', $pa->exchange_rate_id)->first();
                        if ($rateRecord) {
                            echo " (Rate: {$rateRecord->rate})";
                            $rate = $rateRecord->rate;
                        }
                    }
                    echo "\n";
                    
                    // Store for HTML
                    $htmlTransaction['purchase_amounts'][] = [
                        'currency_id' => $pa->currency_id,
                        'amount' => $pa->amount,
                        'rate' => $rate
                    ];
                }
            }
            
            // Check cashback amounts
            $cashbackAmounts = DB::table('cashback_amounts')
                ->where('journal_entry_id', $journalEntry->id)
                ->get();
            
            if ($cashbackAmounts->count() > 0) {
                echo "\n    Cashback Amounts:\n";
                foreach ($cashbackAmounts as $ca) {
                    echo "      - Currency ID {$ca->currency_id}: {$ca->amount}\n";
                    
                    // Store for HTML
                    $htmlTransaction['cashback_amounts'][] = [
                        'currency_id' => $ca->currency_id,
                        'amount' => $ca->amount
                    ];
                }
            }
            
            // Check points entries
            $pointsEntry = DB::table('points_entries')
                ->where('journal_entry_id', $journalEntry->id)
                ->first();
            
            if ($pointsEntry) {
                echo "\n    Points Entry:\n";
                echo "      - Amount: {$pointsEntry->amount}\n";
                echo "      - Balance: {$pointsEntry->balance}\n";
                echo "      - Status: {$pointsEntry->status}\n";
                
                // Store for HTML
                $htmlTransaction['points_entry'] = [
                    'amount' => $pointsEntry->amount,
                    'balance' => $pointsEntry->balance,
                    'status' => $pointsEntry->status
                ];
            }
            
            // Check member aggregates
            $member = DB::table('members')->where('id', $journalEntry->member_id)->first();
            if ($member) {
                $memberAggregate = DB::table('member_aggregates')
                    ->where('member_id', $member->id)
                    ->first();
                
                if ($memberAggregate) {
                    echo "\n    Member Aggregate (Member ID: {$member->id}):\n";
                    echo "      - Transaction Count: {$memberAggregate->transaction_count}\n";
                    echo "      - Purchase Count: {$memberAggregate->purchase_count}\n";
                    
                    // Store for HTML
                    $htmlTransaction['member_aggregate'] = [
                        'member_id' => $member->id,
                        'transaction_count' => $memberAggregate->transaction_count,
                        'purchase_count' => $memberAggregate->purchase_count
                    ];
                }
                
                // Check financial aggregates
                $memberFinancialAggs = DB::table('member_financial_aggregates')
                    ->where('member_id', $member->id)
                    ->get();
                
                if ($memberFinancialAggs->count() > 0) {
                    echo "\n    Member Financial Aggregates:\n";
                    foreach ($memberFinancialAggs as $mfa) {
                        echo "      - Currency ID {$mfa->currency_id}: ";
                        if (isset($mfa->purchase_sum)) echo "Purchase Sum: {$mfa->purchase_sum}, ";
                        if (isset($mfa->cashback_sum)) echo "Cashback Sum: {$mfa->cashback_sum}, ";
                        if (isset($mfa->points_sum)) echo "Points Sum: {$mfa->points_sum}";
                        echo "\n";
                        
                        // Store for HTML
                        $htmlTransaction['member_financial_aggregates'][] = [
                            'currency_id' => $mfa->currency_id,
                            'purchase_sum' => $mfa->purchase_sum ?? null,
                            'cashback_sum' => $mfa->cashback_sum ?? null,
                            'points_sum' => $mfa->points_sum ?? null
                        ];
                    }
                }
            }
        }
        
    } else {
        echo "\n  ✗ No transaction created from this raw transaction\n";
    }
    
    echo "\n" . str_repeat('-', 60) . "\n";
    
    // Add the transaction to our HTML data
    $htmlTransactions[] = $htmlTransaction;
}

// Check for any aggregation jobs that might have been created
echo "\n=== Related Queue Jobs ===\n";
$jobs = DB::table('jobs')
    ->where('payload', 'like', '%"rawTransactionInstanceId":' . $instanceId . '%')
    ->orWhere('payload', 'like', '%"raw_transaction_instance_id":' . $instanceId . '%')
    ->get();

if ($jobs->count() > 0) {
    echo "Found {$jobs->count()} related jobs in queue\n";
    foreach ($jobs as $job) {
        $payload = json_decode($job->payload, true);
        echo "  - Job: " . ($payload['displayName'] ?? 'Unknown') . " (Queue: {$job->queue})\n";
    }
} else {
    echo "No pending jobs found related to this instance\n";
}

// Check for failed jobs
$failedJobs = DB::table('failed_jobs')
    ->where('payload', 'like', '%"rawTransactionInstanceId":' . $instanceId . '%')
    ->orWhere('payload', 'like', '%"raw_transaction_instance_id":' . $instanceId . '%')
    ->get();

if ($failedJobs->count() > 0) {
    echo "\nFound {$failedJobs->count()} failed jobs related to this instance\n";
    foreach ($failedJobs as $job) {
        echo "  - Failed at: {$job->failed_at}\n";
    }
}

// Generate HTML Report
$timestamp = date('Y-m-d H:i:s');
$htmlFile = __DIR__ . '/raw_transaction_trace_' . $instanceId . '.html';

$html = <<<HTML
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raw Transaction Trace - Instance #{$instanceId}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
</head>
<body class="bg-gray-900 text-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="bg-gray-800 rounded-lg shadow-2xl mb-8 p-6 border border-gray-700">
            <h1 class="text-3xl font-bold text-gray-100 mb-4">
                <i class="fas fa-exchange-alt text-blue-400 mr-3"></i>
                Raw Transaction Instance Trace
            </h1>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-900/50 rounded p-3 border border-blue-800">
                    <p class="text-sm text-gray-400">Instance ID</p>
                    <p class="text-xl font-semibold text-blue-400">#{$instanceData['id']}</p>
                </div>
                <div class="bg-green-900/50 rounded p-3 border border-green-800">
                    <p class="text-sm text-gray-400">Status</p>
                    <p class="text-xl font-semibold text-green-400">
                        <i class="fas fa-check-circle mr-1"></i>{$instanceData['status']}
                    </p>
                </div>
                <div class="bg-purple-900/50 rounded p-3 border border-purple-800">
                    <p class="text-sm text-gray-400">Type</p>
                    <p class="text-xl font-semibold text-purple-400">{$instanceData['type']}</p>
                </div>
                <div class="bg-gray-700/50 rounded p-3 border border-gray-600">
                    <p class="text-sm text-gray-400">Created</p>
                    <p class="text-lg font-semibold text-gray-300">{$instanceData['created_at']}</p>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-4">Generated: {$timestamp}</p>
        </div>

        <!-- Raw Transactions -->
        <h2 class="text-2xl font-bold text-gray-100 mb-4">
            <i class="fas fa-list text-indigo-400 mr-2"></i>
            Raw Transactions
        </h2>
HTML;

// Add each transaction to HTML
foreach ($htmlTransactions as $trans) {
    $statusClass = $trans['status'] === 'successful' ? 'text-green-400' : 'text-red-400';
    $statusIcon = $trans['status'] === 'successful' ? 'fa-check-circle' : 'fa-times-circle';
    $statusBg = $trans['status'] === 'successful' ? 'bg-green-900/50 border-green-800' : 'bg-red-900/50 border-red-800';
    
    $html .= <<<HTML
        <div class="bg-gray-800 rounded-lg shadow-2xl mb-6 overflow-hidden border border-gray-700">
            <!-- Transaction Header -->
            <div class="bg-gradient-to-r from-indigo-900 to-purple-900 text-gray-100 p-4 border-b border-gray-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-receipt mr-2"></i>
                        Raw Transaction #{$trans['id']}
                    </h3>
                    <span class="{$statusClass} {$statusBg} px-3 py-1 rounded-full text-sm font-semibold border">
                        <i class="fas {$statusIcon} mr-1"></i>{$trans['status']}
                    </span>
                </div>
                <p class="text-sm mt-1 text-gray-400">Created: {$trans['created_at']}</p>
            </div>
            
            <div class="p-6">
HTML;

    // Add journal entry if exists
    if ($trans['journal_entry']) {
        $je = $trans['journal_entry'];
        $html .= <<<HTML
                <!-- Journal Entry -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-100 mb-3">
                        <i class="fas fa-book text-blue-400 mr-2"></i>
                        Journal Entry #{$je['id']}
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-gray-700/50 rounded p-3 border border-gray-600">
                            <p class="text-xs text-gray-400">Type</p>
                            <p class="font-semibold text-gray-200">{$je['type']}</p>
                        </div>
                        <div class="bg-gray-700/50 rounded p-3 border border-gray-600">
                            <p class="text-xs text-gray-400">Loyalty Type</p>
                            <p class="font-semibold text-gray-200">{$je['loyalty_type']}</p>
                        </div>
                        <div class="bg-gray-700/50 rounded p-3 border border-gray-600">
                            <p class="text-xs text-gray-400">Member ID</p>
                            <p class="font-semibold text-gray-200">#{$je['member_id']}</p>
                        </div>
                        <div class="bg-gray-700/50 rounded p-3 border border-gray-600">
                            <p class="text-xs text-gray-400">Merchant ID</p>
                            <p class="font-semibold text-gray-200">#{$je['merchant_id']}</p>
                        </div>
                    </div>
                </div>
HTML;

        // Add labels if exist
        if (isset($je['labels']) && !empty($je['labels'])) {
            $html .= <<<HTML
                    <div class="mt-3">
                        <p class="text-sm font-semibold text-gray-300 mb-2">
                            <i class="fas fa-tags text-yellow-400 mr-1"></i> Labels:
                        </p>
                        <div class="flex flex-wrap gap-2">
HTML;
            foreach ($je['labels'] as $label) {
                $html .= <<<HTML
                            <span class="bg-yellow-900/50 text-yellow-300 px-3 py-1 rounded-full text-sm border border-yellow-700">
                                {$label}
                            </span>
HTML;
            }
            $html .= <<<HTML
                        </div>
                    </div>
HTML;
        }

        // Add extensions if exist
        if (isset($je['extensions']) && ($je['extensions']['status'] || $je['extensions']['household_id'] || $je['extensions']['description'])) {
            $html .= <<<HTML
                    <div class="mt-3">
                        <p class="text-sm font-semibold text-gray-300 mb-2">
                            <i class="fas fa-puzzle-piece text-purple-400 mr-1"></i> Extensions:
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
HTML;
            if ($je['extensions']['status']) {
                $html .= <<<HTML
                            <div class="bg-purple-900/30 rounded p-2 border border-purple-800">
                                <p class="text-xs text-gray-400">Status</p>
                                <p class="text-sm font-semibold text-purple-300">{$je['extensions']['status']}</p>
                            </div>
HTML;
            }
            if ($je['extensions']['household_id']) {
                $html .= <<<HTML
                            <div class="bg-purple-900/30 rounded p-2 border border-purple-800">
                                <p class="text-xs text-gray-400">Household ID</p>
                                <p class="text-sm font-semibold text-purple-300">#{$je['extensions']['household_id']}</p>
                            </div>
HTML;
            }
            if ($je['extensions']['description']) {
                $html .= <<<HTML
                            <div class="bg-purple-900/30 rounded p-2 border border-purple-800 md:col-span-3">
                                <p class="text-xs text-gray-400">Description</p>
                                <p class="text-sm font-semibold text-purple-300">{$je['extensions']['description']}</p>
                            </div>
HTML;
            }
            $html .= <<<HTML
                        </div>
                    </div>
HTML;
        }

        // Add audit log if exists
        if (isset($je['audit_log'])) {
            $al = $je['audit_log'];
            $html .= <<<HTML
                    <div class="mt-3">
                        <p class="text-sm font-semibold text-gray-300 mb-2">
                            <i class="fas fa-history text-orange-400 mr-1"></i> Audit Log:
                        </p>
                        <div class="bg-orange-900/20 rounded p-3 border border-orange-800">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-400">Service:</span>
                                    <span class="text-orange-300 ml-1 font-semibold">{$al['service']}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Invoker:</span>
                                    <span class="text-orange-300 ml-1 font-semibold">{$al['invoker_type']} #{$al['invoker_id']}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Action:</span>
                                    <span class="text-orange-300 ml-1 font-semibold">{$al['action']}</span>
                                </div>
HTML;
            if ($al['ip_address']) {
                $html .= <<<HTML
                                <div>
                                    <span class="text-gray-400">IP:</span>
                                    <span class="text-orange-300 ml-1 font-semibold">{$al['ip_address']}</span>
                                </div>
HTML;
            }
            $html .= <<<HTML
                            </div>
                        </div>
                    </div>
HTML;
        }
    }

    // Add postings if exist
    if (!empty($trans['postings'])) {
        $html .= <<<HTML
                <!-- Postings -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-100 mb-3">
                        <i class="fas fa-balance-scale text-green-400 mr-2"></i>
                        Double-Entry Postings
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border border-gray-700">
                            <thead class="bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Account</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Currency</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
HTML;
        
        foreach ($trans['postings'] as $posting) {
            $amountClass = $posting['amount'] < 0 ? 'text-red-400' : 'text-green-400';
            $formattedAmount = number_format(abs($posting['amount']), 2);
            $sign = $posting['amount'] < 0 ? '-' : '+';
            
            $html .= <<<HTML
                                <tr class="hover:bg-gray-700/50">
                                    <td class="px-4 py-2 text-sm text-gray-300">{$posting['account']}</td>
                                    <td class="px-4 py-2 text-sm text-gray-300">{$posting['type']}</td>
                                    <td class="px-4 py-2 text-sm text-right {$amountClass} font-mono">{$sign}{$formattedAmount}</td>
                                    <td class="px-4 py-2 text-sm text-gray-300">ID: {$posting['currency_id']}</td>
                                </tr>
HTML;
        }
        
        $html .= <<<HTML
                            </tbody>
                        </table>
                    </div>
                </div>
HTML;
    }

    // Add purchase amounts if exist
    if (!empty($trans['purchase_amounts'])) {
        $html .= <<<HTML
                <!-- Purchase Amounts -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-100 mb-3">
                        <i class="fas fa-money-bill-wave text-green-400 mr-2"></i>
                        Purchase Amounts (Multi-Currency)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
HTML;
        foreach ($trans['purchase_amounts'] as $pa) {
            $formattedAmount = number_format($pa['amount'], 2);
            $rateText = $pa['rate'] ? " (Rate: {$pa['rate']})" : "";
            $html .= <<<HTML
                        <div class="bg-green-900/30 rounded p-3 border border-green-800">
                            <p class="text-xs text-gray-400">Currency ID {$pa['currency_id']}</p>
                            <p class="text-lg font-semibold text-green-400">{$formattedAmount}{$rateText}</p>
                        </div>
HTML;
        }
        $html .= <<<HTML
                    </div>
                </div>
HTML;
    }

    // Add cashback amounts if exist
    if (!empty($trans['cashback_amounts'])) {
        $html .= <<<HTML
                <!-- Cashback Amounts -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-100 mb-3">
                        <i class="fas fa-coins text-yellow-400 mr-2"></i>
                        Cashback Amounts (Multi-Currency)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
HTML;
        foreach ($trans['cashback_amounts'] as $ca) {
            $formattedAmount = number_format($ca['amount'], 2);
            $html .= <<<HTML
                        <div class="bg-yellow-900/30 rounded p-3 border border-yellow-800">
                            <p class="text-xs text-gray-400">Currency ID {$ca['currency_id']}</p>
                            <p class="text-lg font-semibold text-yellow-400">{$formattedAmount}</p>
                        </div>
HTML;
        }
        $html .= <<<HTML
                    </div>
                </div>
HTML;
    }

    // Add points entry if exists
    if ($trans['points_entry']) {
        $pe = $trans['points_entry'];
        $statusBadge = $pe['status'] === 'confirmed' ? 'bg-green-900/50 text-green-400 border-green-800' : 'bg-yellow-900/50 text-yellow-400 border-yellow-800';
        $html .= <<<HTML
                <!-- Points Entry -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-100 mb-3">
                        <i class="fas fa-star text-purple-400 mr-2"></i>
                        Points Entry
                    </h4>
                    <div class="flex space-x-4">
                        <div class="bg-purple-900/30 rounded p-3 border border-purple-800">
                            <p class="text-xs text-gray-400">Amount</p>
                            <p class="text-lg font-semibold text-purple-400">{$pe['amount']}</p>
                        </div>
                        <div class="bg-purple-900/30 rounded p-3 border border-purple-800">
                            <p class="text-xs text-gray-400">Balance</p>
                            <p class="text-lg font-semibold text-purple-400">{$pe['balance']}</p>
                        </div>
                        <div class="bg-purple-900/30 rounded p-3 border border-purple-800">
                            <p class="text-xs text-gray-400">Status</p>
                            <span class="{$statusBadge} px-2 py-1 rounded text-sm font-semibold border">{$pe['status']}</span>
                        </div>
                    </div>
                </div>
HTML;
    }

    // Add member aggregates if exist
    if ($trans['member_aggregate']) {
        $ma = $trans['member_aggregate'];
        $html .= <<<HTML
                <!-- Member Aggregates -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                        Member Aggregates (Member #{$ma['member_id']})
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-blue-50 rounded p-3">
                            <p class="text-xs text-gray-600">Transaction Count</p>
                            <p class="text-2xl font-bold text-blue-700">{$ma['transaction_count']}</p>
                        </div>
                        <div class="bg-blue-50 rounded p-3">
                            <p class="text-xs text-gray-600">Purchase Count</p>
                            <p class="text-2xl font-bold text-blue-700">{$ma['purchase_count']}</p>
                        </div>
                    </div>
                </div>
HTML;
    }

    // Add member financial aggregates if exist
    if (!empty($trans['member_financial_aggregates'])) {
        $html .= <<<HTML
                <!-- Member Financial Aggregates -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fas fa-calculator text-indigo-500 mr-2"></i>
                        Member Financial Aggregates
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Currency</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Purchase Sum</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Cashback Sum</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-600 uppercase tracking-wider">Points Sum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
HTML;
        foreach ($trans['member_financial_aggregates'] as $mfa) {
            $purchaseSum = $mfa['purchase_sum'] !== null ? number_format($mfa['purchase_sum'], 2) : '-';
            $cashbackSum = $mfa['cashback_sum'] !== null ? number_format($mfa['cashback_sum'], 2) : '-';
            $pointsSum = $mfa['points_sum'] !== null ? number_format($mfa['points_sum'], 2) : '-';
            
            $html .= <<<HTML
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm">Currency ID {$mfa['currency_id']}</td>
                                    <td class="px-4 py-2 text-sm text-right font-mono">{$purchaseSum}</td>
                                    <td class="px-4 py-2 text-sm text-right font-mono">{$cashbackSum}</td>
                                    <td class="px-4 py-2 text-sm text-right font-mono">{$pointsSum}</td>
                                </tr>
HTML;
        }
        $html .= <<<HTML
                            </tbody>
                        </table>
                    </div>
                </div>
HTML;
    }

    $html .= <<<HTML
            </div>
        </div>
HTML;
}

// Close HTML
$html .= <<<HTML
    </div>
</body>
</html>
HTML;

// Write HTML file
file_put_contents($htmlFile, $html);
echo "\n\n✅ HTML report generated: {$htmlFile}\n";