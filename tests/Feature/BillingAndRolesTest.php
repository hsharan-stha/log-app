<?php

namespace Tests\Feature;

use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingAndRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_access_academics_but_not_admin_dashboard(): void
    {
        $hr = User::factory()->create(['role' => 'hr']);

        $this->actingAs($hr)->get(route('admin.academics.index'))->assertOk();
        $this->actingAs($hr)->get(route('admin.students.index'))->assertOk();
        $this->actingAs($hr)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_finance_can_create_invoice_and_record_payment(): void
    {
        $finance = User::factory()->create(['role' => 'finance']);
        $student = User::factory()->create(['role' => 'student']);
        $fee = FeeType::query()->create([
            'name' => 'Tuition',
            'code' => 'tuition',
            'default_amount' => 10000,
            'currency' => 'JPY',
            'frequency' => 'monthly',
            'is_active' => true,
        ]);

        $this->actingAs($finance)
            ->post(route('finance.invoices.store'), [
                'student_id' => $student->id,
                'fee_type_id' => $fee->id,
                'title' => 'Tuition September',
                'amount' => 10000,
                'currency' => 'JPY',
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(10)->toDateString(),
                'status' => 'issued',
            ])
            ->assertRedirect();

        $invoice = Invoice::query()->first();
        $this->assertNotNull($invoice);
        $this->assertSame('issued', $invoice->status);

        $this->actingAs($finance)
            ->post(route('finance.invoices.payments.store', $invoice), [
                'amount' => 4000,
                'method' => 'cash',
                'paid_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('finance.invoices.show', $invoice));

        $invoice->refresh();
        $this->assertSame('partial', $invoice->status);
        $this->assertSame(6000, $invoice->balanceDue());
    }

    public function test_guardian_can_view_ward_invoice_only(): void
    {
        $guardian = User::factory()->create(['role' => 'guardian']);
        $student = User::factory()->create(['role' => 'student']);
        $other = User::factory()->create(['role' => 'student']);
        $guardian->wards()->attach($student->id);

        $own = Invoice::query()->create([
            'number' => 'INV-TEST-0001',
            'student_id' => $student->id,
            'title' => 'Fees',
            'amount' => 5000,
            'currency' => 'JPY',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'issued',
        ]);

        $foreign = Invoice::query()->create([
            'number' => 'INV-TEST-0002',
            'student_id' => $other->id,
            'title' => 'Fees',
            'amount' => 5000,
            'currency' => 'JPY',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'issued',
        ]);

        $this->actingAs($guardian)->get(route('guardian.billing.index'))->assertOk();
        $this->actingAs($guardian)->get(route('guardian.billing.show', $own))->assertOk();
        $this->actingAs($guardian)->get(route('guardian.billing.show', $foreign))->assertForbidden();
    }

    public function test_staff_and_other_land_on_office_dashboard(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $other = User::factory()->create(['role' => 'other']);

        $this->actingAs($staff)->get('/dashboard')->assertRedirect(route('office.dashboard'));
        $this->actingAs($other)->get('/dashboard')->assertRedirect(route('office.dashboard'));
        $this->actingAs($staff)->get(route('office.dashboard'))->assertOk();
    }

    public function test_teacher_cannot_access_finance(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher)->get(route('finance.dashboard'))->assertForbidden();
    }
}
