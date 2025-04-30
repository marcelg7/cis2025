<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model {
    protected $guarded = [];

    // Add date casting
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_date' => 'date',
        'first_bill_date' => 'date',
    ];


    public function subscriber() {
        return $this->belongsTo(Subscriber::class);
    }

    public function activityType() {
        return $this->belongsTo(ActivityType::class);
    }

    public function device() {
        return $this->belongsTo(Device::class);
    }

    public function plan() {
        return $this->belongsTo(Plan::class);
    }

    public function commitmentPeriod() {
        return $this->belongsTo(CommitmentPeriod::class);
    }

    public function addOns() {
        return $this->hasMany(ContractAddOn::class);
    }

    public function oneTimeFees() {
        return $this->hasMany(ContractOneTimeFee::class);
    }
}
