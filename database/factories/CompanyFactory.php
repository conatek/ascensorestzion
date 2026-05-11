<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $provinces = Province::all()->modelKeys();
        $cities = City::all()->modelKeys();


        return [
            'company_name' => $this->faker->company(),
            'company_type_id' => $this->faker->randomElement([1, 2]),
            'identification_type_id' => $this->faker->randomElement([1, 2, 3, 4, 5, 6]),
            'identification_number' => $this->faker->numberBetween(10000000, 99999999),
            'province_id' => $this->faker->randomElement($provinces),
            'city_id' => $this->faker->randomElement($cities),
            'address' => $this->faker->address(),
            'industry_type_id' => $this->faker->randomElement([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
            'size' => $this->faker->numberBetween(50, 200),
            'founded_at' => $this->faker->dateTime(),
            'is_active' => $this->faker->randomElement([0, 1]),
            'description' => $this->faker->paragraph,
            // 'logo_public_id' => $this->faker->string,
            // 'logo_url' => $this->faker->url(),

            'contact_name' => $this->faker->name(),
            'contact_first_surname' => $this->faker->lastName(),
            'contact_second_surname' => $this->faker->lastName(),
            'website' => $this->faker->url(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'cellphone' => $this->faker->e164PhoneNumber(),

            'facebook' => 'https://www.facebook.com/' . $this->faker->userName,
            'linkedin' => 'https://www.linkedin.com/' . $this->faker->userName,
            'instagram' => 'https://www.instagram.com/' . $this->faker->userName,
            'x_twitter' => 'https://www.x.com/' . $this->faker->userName,
            'youtube' => 'https://www.youtube.com/' . $this->faker->userName,

            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}
