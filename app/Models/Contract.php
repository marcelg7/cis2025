<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model {
    protected $guarded = [];

	use HasFactory;

	protected $fillable = [
			'subscriber_id',
			'start_date',
			'end_date',
			'activity_type_id',
			'contract_date',
			'location',
			'shortcode_id',
			'agreement_credit_amount',
			'required_upfront_payment',
			'optional_down_payment',
			'deferred_payment_amount',
			'commitment_period_id',
			'first_bill_date',
			'status',
			'bell_device_id',           
			'bell_pricing_type',        
			'bell_tier',                
			'bell_retail_price',        
			'bell_monthly_device_cost', 
			'bell_plan_cost',           
			'bell_dro_amount',          
			'bell_plan_plus_device',    			
			'manufacturer',
			'model',
			'version',
			'device_storage',
			'extra_info',
			'device_price',
			'pdf_path',
			'signature_path',
			'updated_by',
    ];
	
    // Add date casting
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_date' => 'date',
        'first_bill_date' => 'date',
		'is_test' => 'boolean',
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
		
	}