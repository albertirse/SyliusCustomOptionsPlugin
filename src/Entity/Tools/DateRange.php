<?php
declare(strict_types=1);

namespace Brille24\CustomerOptionsPlugin\Entity\Tools;


use DateTimeInterface;
use InvalidArgumentException;

class DateRange implements DateRangeInterface
{
    /** @var int|null */
    private $id;

    /**
     * @var DateTimeInterface $start
     * @var DateTimeInterface $end
     */
    private $start, $end;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id):void
    {
        $this->id = $id;
    }

    public function __construct(DateTimeInterface $start, DateTimeInterface $end)
    {
        if ($end < $start) {
            throw new InvalidArgumentException('End can\'t be greater than start');
        }

        $this->start = $start;
        $this->end   = $end;
    }

    /** {@inheritdoc} */
    public function contains(DateTimeInterface $current): bool
    {
        $afterStart = $this->start <= $current;
        $beforeEnd = $this->end >= $current;
        return $afterStart && $beforeEnd;
    }
}