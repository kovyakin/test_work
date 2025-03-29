<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 /**
 * @OA\Schema(
 *  schema="Booking",
 *  title="Sample schema for using references",
 * 	@OA\Property(
 *      property="user_id",
 *      type="integer",
 *    ),
 * 	@OA\Property(
 *      property="resource_id",
 *      type="integer",
 *    ),
 *  @OA\Property(
 *      property="start_time",
 *      type="datetime"
 *    ),
 *       @OA\Property(
 *       property="end_time",
 *       type="datetime"
 *     )
 * )
 */
class Bookings extends Model
{
    /** @use HasFactory<\Database\Factories\BookingsFactory> */
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'resource_id',
        'user_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime:d-m-Y H:i:s',
        'end_time' => 'datetime:d-m-Y H:i:s',
        'created_at' => 'datetime:d-m-Y H:i:s',
        'updated_at' => 'datetime:d-m-Y H:i:s',
    ];

    public function resources(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }
}
