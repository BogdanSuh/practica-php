<?php
namespace Model;

use Illuminate\Database\Eloquent\Model;

class BookCopy extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'copy_id';
    protected $table = 'book_copies';

    protected $fillable = [
        'book_title',
        'author',
        'year',
        'qr_code',
        'shelf_location',
        'status',
        'has_electronic_version',
        'electronic_link',
        'cover_image'
    ];

    // Связь с бронированиями
    public function bookings()
    {
        return $this->hasMany(Book::class, 'copy_id', 'copy_id');
    }

    // Проверка доступности
    public function isAvailable(): bool
    {
        return $this->status === 'in_hall';
    }

    // Получить активное бронирование
    public function getActiveBooking()
    {
        return $this->bookings()
            ->whereIn('status', ['reserved', 'issued'])
            ->first();
    }

    // Изменить статус
    public function changeStatus(string $status): void
    {
        if (in_array($status, ['in_hall', 'issued', 'reserved'])) {
            $this->status = $status;
            $this->save();
        }
    }
}