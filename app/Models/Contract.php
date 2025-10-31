<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contract extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'subscriber_id',
        'start_date',
        'end_date',
        'activity_type_id',
        'contract_date',
        'location',
        'location_id',
        'customer_phone',
        'shortcode_id',
        'agreement_credit_amount',
        'required_upfront_payment',
        'optional_down_payment',
        'deferred_payment_amount',
        'commitment_period_id',
        'first_bill_date',
        'status',
        'bell_device_id',
        'device_name',
        'bell_pricing_type',
        'bell_tier',
        'bell_retail_price',
        'bell_monthly_device_cost',
        'bell_plan_cost',
        'bell_dro_amount',
        'bell_plan_plus_device',
        'rate_plan_id',
        'mobile_internet_plan_id',
        'rate_plan_price',
        'mobile_internet_price',
        'selected_tier',
        'manufacturer',
        'model',
        'version',
        'device_storage',
        'extra_info',
        'device_price',
        'pdf_path',
        'signature_path',
        'updated_by',
        'financing_status',
        'financing_signature_path',
        'financing_signed_at',
        'financing_pdf_path',
        'financing_csr_initials_path',
        'financing_csr_initialed_at',
        'custom_device_name',
        'dro_status',
        'dro_signature_path',
        'dro_csr_initials_path',
        'dro_signed_at',
        'dro_csr_initialed_at',
        'dro_pdf_path',
		'imei',
        'ftp_to_vault',
        'ftp_at',
        'vault_path',
        'ftp_error',
    ];

    // Add date casting
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_date' => 'date',
        'first_bill_date' => 'date',
        'financing_signed_at' => 'datetime',
        'ftp_at' => 'datetime',
        'is_test' => 'boolean',
        'ftp_to_vault' => 'boolean',
        'device_price' => 'decimal:2',
        'agreement_credit_amount' => 'decimal:2',
        'required_upfront_payment' => 'decimal:2',
        'optional_down_payment' => 'decimal:2',
        'deferred_payment_amount' => 'decimal:2',
        'bell_retail_price' => 'decimal:2',
        'bell_monthly_device_cost' => 'decimal:2',
        'bell_plan_cost' => 'decimal:2',
        'bell_dro_amount' => 'decimal:2',
        'bell_plan_plus_device' => 'decimal:2',
        'rate_plan_price' => 'decimal:2',
        'mobile_internet_price' => 'decimal:2',
        'dro_signed_at' => 'datetime',
        'dro_csr_initialed_at' => 'datetime',
    ];

    protected $dates = ['start_date', 'contract_date', 'first_bill_date', 'end_date'];

    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function activityType()
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function bellDevice(): BelongsTo
    {
        return $this->belongsTo(BellDevice::class);
    }

    public function commitmentPeriod()
    {
        return $this->belongsTo(CommitmentPeriod::class);
    }

    public function addOns()
    {
        return $this->hasMany(ContractAddOn::class);
    }

    public function oneTimeFees()
    {
        return $this->hasMany(ContractOneTimeFee::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the rate plan for this contract
     */
    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    /**
     * Get the mobile internet plan for this contract
     */
    public function mobileInternetPlan(): BelongsTo
    {
        return $this->belongsTo(MobileInternetPlan::class);
    }

    /**
     * Get the location for this contract
     */
    public function locationModel(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Get the total monthly rate for cellular services
     */
    public function getTotalCellularRateAttribute(): float
    {
        $total = 0;
        if ($this->rate_plan_price) {
            $total += $this->rate_plan_price;
        }
        if ($this->mobile_internet_price) {
            $total += $this->mobile_internet_price;
        }
        return $total;
    }

    public function requiresFinancing(): bool
    {
        // Requires financing if:
        // 1. Has a Bell device
        // 2. Has device financing (retail price - credits - payments > 0)
        if (!$this->bell_device_id) {
            return false;
        }
        $financedAmount = $this->getTotalFinancedAmount();
        return $financedAmount > 0;
    }

    /**
     * Check if this contract requires a DRO form
     */
    public function requiresDro(): bool
    {
        // Requires DRO form if bell_dro_amount > 0
        return ($this->bell_dro_amount ?? 0) > 0;
    }

    /**
     * Get total financed amount
     */
    public function getTotalFinancedAmount(): float
    {
        $devicePrice = $this->bell_retail_price ?? 0;
        $credit = $this->agreement_credit_amount ?? 0;
        $upfront = $this->required_upfront_payment ?? 0;
        $downPayment = $this->optional_down_payment ?? 0;
        return max(0, $devicePrice - $credit - $upfront - $downPayment);
    }

    /**
     * Get monthly device payment
     */
    public function getMonthlyDevicePayment(): float
    {
        $financedAmount = $this->getTotalFinancedAmount();
        $deferred = $this->deferred_payment_amount ?? 0;
        return ($financedAmount - $deferred) / 24;
    }

    protected static $logAttributes = ['*']; // Log all attributes, or specify e.g. ['rate_plan_id', 'bell_pricing_type']

    // Fixed activity log configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // or ->logOnly(['specific', 'fields']) if you prefer
            ->logOnlyDirty()
            ->useLogName('contract')
            ->dontSubmitEmptyLogs();
    }
}