<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'folder_id' => 1,
            'mime_type' => 'application/pdf',
            'size' => 12345,
            'extension' => 'pdf',
            'filename' => $this->faker->sentence(15),
            'cloud_root_directory' => $this->faker->sentence(10),
            'cloud_public_url' => $this->faker->sentence(10),
        ];
    }
}
