<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Database;

class SQL
{
    public const INNER_JOIN = 1;

    public const LEFT_JOIN = 2;

    public const RIGHT_JOIN = 3;

    public const FULL_JOIN = 4;

    public const SELECT_SQL = 1;

    public const INSERT_SQL = 2;

    public const UPDATE_SQL = 3;

    public const DELETE_SQL = 4;

    public const FETCH_ONE = 1;

    public const FETCH_ALL = 2;

    public const FETCH_COLUMN = 3;

    public const FULL_TEXT_NATURAL_LANGUAGE = 1;

    public const FULL_TEXT_NATURAL_LANGUAGE_WITH_QUERY = 2;

    public const FULL_TEXT_BOOLEAN = 3;

    public const FULL_TEXT_WITH_QUERY = 4;
}
