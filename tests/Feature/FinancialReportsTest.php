<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinancialReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_profit_loss_report_page_is_accessible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.profit-loss'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) =>
            $page->component('Reports/ProfitLoss')
        );
    }

    public function test_balance_sheet_report_page_is_accessible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.balance-sheet'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) =>
            $page->component('Reports/BalanceSheet')
        );
    }

    public function test_cash_flow_report_page_is_accessible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.cash-flow'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) =>
            $page->component('Reports/CashFlow')
        );
    }
}
