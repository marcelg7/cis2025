<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
			'plan_id',
			'commitment_period_id',
			'first_bill_date',
			'status',
			'manufacturer',
			'model',
			'version',
			'device_storage',
			'extra_info',
			'device_price',
			'pdf_path',
			'signature_path',
    ];
	
    // Add date casting
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_date' => 'date',
        'first_bill_date' => 'date',
		'is_test' => 'boolean',
    ];



	protected $dates = ['start_date', 'contract_date', 'first_bill_date', 'end_date'];

	public function subscriber()
		{
			return $this->belongsTo(Subscriber::class);
		}

		public function plan()
		{
			return $this->belongsTo(Plan::class);
		}

		public function activityType()
		{
			return $this->belongsTo(ActivityType::class);
		}

		public function device()
		{
			return $this->belongsTo(Device::class);
		}

		public function commitmentPeriod()
		{
			return $this->belongsTo(CommitmentPeriod::class);
		}

		public function shortcode()
		{
			return $this->belongsTo(Shortcode::class);
		}

		public function addOns()
		{
			return $this->hasMany(ContractAddOn::class);
		}

		public function oneTimeFees()
		{
			return $this->hasMany(ContractOneTimeFee::class);
		}
	}