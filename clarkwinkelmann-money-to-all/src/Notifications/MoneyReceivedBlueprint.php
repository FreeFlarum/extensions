<?php

namespace ClarkWinkelmann\MoneyToAll\Notifications;

use Flarum\Notification\AlertableInterface;
use ClarkWinkelmann\MoneyToAll\Record;
use Flarum\Notification\Blueprint\BlueprintInterface;

class MoneyReceivedBlueprint implements BlueprintInterface, AlertableInterface
{
    public function __construct(protected Record $record, protected float $amount, protected ?string $message = null)
    {
    }

    public function getFromUser(): ?\Flarum\User\User
    {
        return null;
    }

    public function getSubject(): ?\Flarum\Database\AbstractModel
    {
        return $this->record;
    }

    public function getData(): mixed
    {
        return [
            'amount' => $this->amount,
            'message' => $this->message,
        ];
    }

    public static function getType(): string
    {
        return 'moneyToAll';
    }

    public static function getSubjectModel(): string
    {
        return Record::class;
    }
}
