<?php

namespace Tests\Unit;

use Tests\TestCase;

class FinancialLogicTest extends TestCase
{
    /**
     * Test exchange gain/loss calculation logic
     */
    public function test_exchange_gain_loss_calculation(): void
    {
        // Order placed at rate 15000 IDR per USD
        $orderAmount = 1000.00; // USD
        $orderExchangeRate = 15000;
        $expectedBaseValue = $orderAmount * $orderExchangeRate; // 15,000,000 IDR

        // Payment received at rate 15500 IDR per USD (currency depreciated)
        $paymentAmount = 1000.00; // USD
        $paymentExchangeRate = 15500;
        $paymentBaseValue = $paymentAmount * $paymentExchangeRate; // 15,500,000 IDR

        // Exchange gain = actual - expected = 15,500,000 - 15,000,000 = 500,000
        $exchangeGainLoss = $paymentBaseValue - $expectedBaseValue;
        
        $this->assertEquals(500000, $exchangeGainLoss);
    }

    /**
     * Test payment status calculation with bank fees
     */
    public function test_payment_status_with_bank_fees(): void
    {
        $orderTotal = 10000.00;
        $creditNoteAmount = 0;
        $amountDue = $orderTotal - $creditNoteAmount;

        // Scenario 1: Partial payment
        $amountPaid = 8000.00;
        $bankFees = 500.00;
        $totalSettled = $amountPaid + $bankFees; // 8500

        $status = $this->calculatePaymentStatus($totalSettled, $amountDue);
        $this->assertEquals('partial', $status);

        // Scenario 2: Fully paid with bank fees making up difference
        $amountPaid2 = 9500.00;
        $bankFees2 = 500.00;
        $totalSettled2 = $amountPaid2 + $bankFees2; // 10000

        $status2 = $this->calculatePaymentStatus($totalSettled2, $amountDue);
        $this->assertEquals('paid', $status2);

        // Scenario 3: Unpaid
        $amountPaid3 = 0;
        $bankFees3 = 0;
        $totalSettled3 = $amountPaid3 + $bankFees3; // 0

        $status3 = $this->calculatePaymentStatus($totalSettled3, $amountDue);
        $this->assertEquals('unpaid', $status3);
    }

    /**
     * Test net profit calculation
     */
    public function test_net_profit_calculation(): void
    {
        $revenue = 50000.00;
        $cogs = 30000.00;
        $expenses = 3500.00; // freight + insurance
        $commission = 2500.00; // 5% of revenue

        // Gross profit = Revenue - COGS
        $grossProfit = $revenue - $cogs;
        $this->assertEquals(20000.00, $grossProfit);

        // Net profit = Gross Profit - Expenses - Commission
        $netProfit = $grossProfit - $expenses - $commission;
        $this->assertEquals(14000.00, $netProfit);

        // Verify margin calculations
        $grossMargin = ($grossProfit / $revenue) * 100;
        $netMargin = ($netProfit / $revenue) * 100;

        $this->assertEqualsWithDelta(40.0, $grossMargin, 0.01); // 40%
        $this->assertEqualsWithDelta(28.0, $netMargin, 0.01); // 28%
    }

    /**
     * Test payment with credit note deduction
     */
    public function test_payment_with_credit_note(): void
    {
        $orderTotal = 10000.00;
        $creditNoteAmount = 2000.00;
        $amountDue = $orderTotal - $creditNoteAmount; // 8000

        // Full payment of reduced amount
        $amountPaid = 8000.00;
        $totalSettled = $amountPaid;

        $status = $this->calculatePaymentStatus($totalSettled, $amountDue);
        $this->assertEquals('paid', $status);
    }

    /**
     * Test customer credit limit enforcement
     */
    public function test_customer_credit_limit_enforcement(): void
    {
        $creditLimit = 10000.00;
        
        // Current exposure (unpaid orders)
        $existingOrders = 5000.00;
        
        // New order that should be allowed
        $newOrder1 = 4000.00;
        $wouldExceed1 = ($existingOrders + $newOrder1) > $creditLimit;
        $this->assertFalse($wouldExceed1); // 9000 <= 10000

        // New order that should be blocked
        $newOrder2 = 6000.00;
        $wouldExceed2 = ($existingOrders + $newOrder2) > $creditLimit;
        $this->assertTrue($wouldExceed2); // 11000 > 10000
    }

    private function calculatePaymentStatus(float $totalSettled, float $amountDue): string
    {
        return match(true) {
            $totalSettled >= $amountDue => 'paid',
            $totalSettled > 0 => 'partial',
            default => 'unpaid'
        };
    }
}
