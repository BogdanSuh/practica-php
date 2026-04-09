<?php
namespace Model;

use Illuminate\Database\Eloquent\Model;
use Src\Auth\IdentityInterface;

class Users extends Model implements IdentityInterface
{
    public $timestamps = false;
    protected $primaryKey = 'user_id';
    protected $table = 'users';

    protected $fillable = [
        'name',
        'login',
        'password',
        'role',
        'reader_card'
    ];

    protected $hidden = [
        'password'
    ];

    // Метод для аутентификации
    public function attemptIdentity(array $credentials)
    {
        $user = self::where('login', $credentials['login'])->first();

        if ($user && password_verify($credentials['password'], $user->password)) {
            return $user;
        }

        return null;
    }

    // Метод поиска пользователя по ID
    public function findIdentity(int $id)
    {
        return self::where('user_id', $id)->first();
    }

    // Получение ID пользователя
    public function getId(): int
    {
        return $this->user_id;
    }

    // Связь с бронированиями
    public function bookings()
    {
        return $this->hasMany(Book::class, 'user_id', 'user_id');
    }

    // Получить активные бронирования пользователя
    public function getActiveBookings()
    {
        return $this->bookings()
            ->whereIn('status', ['reserved', 'issued'])
            ->orderBy('booking_date', 'DESC')
            ->get();
    }

    // Получить историю пользователя
    public function getHistory()
    {
        return $this->bookings()
            ->where('status', 'returned')
            ->orderBy('return_date', 'DESC')
            ->get();
    }
}