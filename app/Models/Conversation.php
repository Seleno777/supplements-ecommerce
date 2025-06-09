<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
    ];

    protected $appends = [
        'other_user'
    ];

    /**
     * Relación con el primer usuario de la conversación
     */
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    /**
     * Relación con el segundo usuario de la conversación
     */
    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    /**
     * Mensajes de la conversación (ordenados por los más recientes primero)
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'desc');
    }

    /**
     * Accesor para obtener el otro usuario en la conversación
     *
     * @return \App\Models\User|null
     */
    public function getOtherUserAttribute()
    {
        // Verificación segura de autenticación
        if (!Auth::check()) {
            return null;
        }

        // Obtenemos el ID del usuario autenticado de manera segura
        $authId = Auth::id();

        // Comparamos con los IDs de la conversación
        if ($this->user_one_id === $authId) {
            return $this->userTwo;
        }

        if ($this->user_two_id === $authId) {
            return $this->userOne;
        }

        return null;
    }

    /**
     * Verifica si un usuario específico participa en esta conversación
     *
     * @param int $userId
     * @return bool
     */
    public function hasUser(int $userId): bool
    {
        return $this->user_one_id === $userId || $this->user_two_id === $userId;
    }

    /**
     * Obtiene la conversación entre dos usuarios o crea una nueva si no existe
     *
     * @param int $userOneId
     * @param int $userTwoId
     * @return \App\Models\Conversation
     */
    public static function betweenUsers(int $userOneId, int $userTwoId): Conversation
{
    // 🚩 Validación PRO → evitar conversación consigo mismo
    if ($userOneId === $userTwoId) {
        throw new \Exception('No se puede crear conversación consigo mismo.');
    }

    // 🚩 Siempre normalizamos → el menor va en user_one_id
    $smallerId = min($userOneId, $userTwoId);
    $largerId = max($userOneId, $userTwoId);

    return self::firstOrCreate([
        'user_one_id' => $smallerId,
        'user_two_id' => $largerId,
    ]);
}


    public function scopeBetweenUsers($query, $userId1, $userId2)
{
    return $query->where(function ($q) use ($userId1, $userId2) {
        $q->where('user_one_id', $userId1)->where('user_two_id', $userId2);
    })->orWhere(function ($q) use ($userId1, $userId2) {
        $q->where('user_one_id', $userId2)->where('user_two_id', $userId1);
    })->firstOrCreate([
        'user_one_id' => min($userId1, $userId2),
        'user_two_id' => max($userId1, $userId2),
    ]);
}
}