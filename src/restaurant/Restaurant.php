<?php

declare(strict_types=1);

namespace Restaurant;

/**
 * Class Restaurant
 * @package App\Entity
 */
class Restaurant
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var MenuItem[]
     */
    private $menuItems;

    /**
     * Restaurant constructor.
     * @param int $id
     * @param string $name
     * @param array $menuItems
     */
    public function __construct(int $id, string $name, array $menuItems = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->menuItems = $menuItems;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return MenuItem[]
     */
    public function getMenuItems(): array
    {
        return $this->menuItems;
    }

    /**
     * @param MenuItem[] $menuItems
     */
    public function setMenuItems(array $menuItems): void
    {
        $this->menuItems = $menuItems;
    }

    /**
     * @param MenuItem $menuItems
     */
    public function appendMenuItem(MenuItem $menuItems): void
    {
        $this->menuItems[] = $menuItems;
    }
}