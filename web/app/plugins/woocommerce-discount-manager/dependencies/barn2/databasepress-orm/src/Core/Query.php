<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * @package   Barn2\DatabasepressORM
 * @copyright 2018 Zindex Software
 * @copyright Barn2
 * @license   GPLv3
 */
namespace Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Core;

use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\BaseStatement;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\HavingStatement;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\Databasepress\SQL\SQLStatement;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Traits\LoaderTrait;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Traits\SelectTrait;
use Barn2\Plugin\Discount_Manager\Dependencies\Barn2\DatabasepressORM\Traits\SoftDeletesTrait;
class Query extends BaseStatement
{
    use SelectTrait {
        select as protected;
    }
    use SoftDeletesTrait;
    use LoaderTrait;
    /** @var HavingStatement */
    protected $have;
    /**
     * Query constructor.
     *
     * @param SQLStatement|null $statement
     */
    public function __construct(SQLStatement $statement = null)
    {
        parent::__construct($statement);
        $this->have = new HavingStatement($this->sql);
    }
    /**
     * @return HavingStatement
     */
    protected function getHavingStatement() : HavingStatement
    {
        return $this->have;
    }
    /**
     * @inheritDoc
     */
    public function __clone()
    {
        parent::__clone();
        $this->have = new HavingStatement($this->sql);
    }
}
