<?php
namespace Model;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'booking_id';
    protected $table = 'bookings';

    protected $fillable = [
        'user_id',
        'copy_id',
        'booking_date',
        'issue_date',
        'due_date',
        'return_date',
        'status'
    ];

    protected $casts = [
        'booking_date' => 'datetime',
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'return_date' => 'datetime'
    ];

    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    // Связь с экземпляром книги
    public function copy()
    {
        return $this->belongsTo(BookCopy::class, 'copy_id', 'copy_id');
    }

    // Проверка просрочки
    public function isOverdue(): bool
    {
        if ($this->status !== 'issued') {
            return false;
        }

        $now = new \DateTime();
        $dueDate = $this->due_date instanceof \DateTime ? $this->due_date : new \DateTime($this->due_date);

        return $now > $dueDate;
    }

    // Продление книги на указанное количество дней
    public function extend(int $days = 7): void
    {
        $dueDate = $this->due_date instanceof \DateTime ? $this->due_date : new \DateTime($this->due_date);
        $dueDate->modify("+{$days} days");
        $this->due_date = $dueDate->format('Y-m-d H:i:s');
        $this->save();
    }

// Получить количество продлений (если есть поле extend_count)
    public function getExtendCount(): int
    {
        // Если у вас есть поле extend_count в таблице bookings
        return $this->extend_count ?? 0;
    }

// Увеличить счетчик продлений
    public function incrementExtendCount(): void
    {
        if (property_exists($this, 'extend_count')) {
            $this->extend_count = ($this->extend_count ?? 0) + 1;
            $this->save();
        }
    }
    // Получить активные бронирования для экземпляра
    public static function getActiveForCopy($copyId)
    {
        return self::where('copy_id', $copyId)
            ->whereIn('status', ['reserved', 'issued'])
            ->first();
    }
    // Проверить, есть ли очередь на книгу
    public static function hasQueue(int $copyId, ?int $excludeBookingId = null): bool
    {
        $query = self::where('copy_id', $copyId)
            ->where('status', 'reserved');

        if ($excludeBookingId) {
            $query->where('booking_id', '!=', $excludeBookingId);
        }

        return $query->exists();
    }

// Получить позицию в очереди
    public static function getQueuePosition(int $copyId, int $bookingId): int
    {
        $queue = self::where('copy_id', $copyId)
            ->where('status', 'reserved')
            ->where('booking_id', '!=', $bookingId)
            ->orderBy('booking_date', 'asc')
            ->get();

        $position = 1;
        foreach ($queue as $item) {
            if ($item->booking_id == $bookingId) {
                return $position;
            }
            $position++;
        }

        return 0;
    }
}