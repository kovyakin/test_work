<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

///**
// * @OA\Definition(
// *  definition="Resources",
// *  @OA\Property(
// *      property="name",
// *      type="string"
// *  ),
// *  @OA\Property(
// *      property="type",
// *      type="string"
// *  ),
// *  @OA\Property(
// *      property="description",
// *      type="string"
// *  )
// * )
// */

/**
 * @OA\Schema(
 *  schema="Resources",
 *  title="Sample schema for using references",
 * 	@OA\Property(
 *      property="name",
 *      type="string"
 *    ),
 * 	@OA\Property(
 *      property="type",
 *      type="string"
 *    ),
 *  @OA\Property(
 *      property="description",
 *      type="string"
 *    )
 * )
 */
class Resource extends Model
{
    /** @use HasFactory<\Database\Factories\ResourcesFactory> */
    use HasFactory;

    protected $table = 'resources';

    protected $fillable = [
        'name',
        'type',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime:d-m-Y H:i:s',
        'updated_at' => 'datetime:d-m-Y H:i:s',
    ];

    public function bookings()
    {
        return $this->hasMany(Bookings::class, 'resource_id', 'id');
    }
}
