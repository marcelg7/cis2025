<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    protected $model = \App\Models\Customer::class;

	public function definition()
	{
		$firstName = $this->faker->firstName;
		$lastName = $this->faker->lastName;
		$isIndividual = $this->faker->boolean(70);
		return [
			'ivue_customer_number' => $this->faker->unique()->numerify('502#####'),
			'first_name' => $isIndividual ? $firstName : $this->faker->word, // Ensure non-empty
			'last_name' => $isIndividual ? $lastName : $this->faker->word, // Ensure non-empty
			'email' => $this->faker->unique()->safeEmail,
			'address' => $this->faker->streetAddress,
			'city' => $this->faker->city,
			'state' => 'ON',
			'zip_code' => $this->faker->regexify('N[0-9][A-Z][0-9][A-Z][0-9]'),
			'display_name' => $isIndividual ? "$firstName $lastName" : $this->faker->company,
			'is_individual' => $isIndividual,
			'customer_json' => json_encode([
				'isIndividual' => $isIndividual,
				'customer' => $this->faker->unique()->numerify('502#####'),
				'accounts' => [$this->faker->numerify('102#####'), $this->faker->numerify('102#####')],
				'isDefault' => false,
				'displayName' => $isIndividual ? "$firstName $lastName" : $this->faker->company,
				'address' => [
					'lineOne' => $this->faker->streetAddress,
					'lineTwo' => $this->faker->optional()->secondaryAddress,
					'city' => $this->faker->city,
					'state' => 'ON',
					'zipCode' => $this->faker->regexify('N[0-9][A-Z][0-9][A-Z][0-9]'),
					'zip4' => '0',
					'description' => '',
				],
				'firstName' => $isIndividual ? $firstName : '',
				'middleName' => '',
				'lastName' => $isIndividual ? $lastName : '',
				'users' => [$this->faker->safeEmail],
			]),
			'last_fetched_at' => now(),
		];
	}
}