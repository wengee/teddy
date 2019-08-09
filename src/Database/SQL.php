<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-08 16:53:20 +0800
 */

namespace Teddy\Database;

class SQL
{
    const INNER_JOIN = 1;

    const LEFT_JOIN = 2;

    const RIGHT_JOIN = 3;

    const FULL_JOIN = 4;

    const SELECT_SQL = 1;

    const INSERT_SQL = 2;

    const UPDATE_SQL = 3;

    const DELETE_SQL = 4;

    const FETCH_ONE = 1;

    const FETCH_ALL = 2;

    const FETCH_COLUMN = 3;

    const FULL_TEXT_NATURAL_LANGUAGE = 1;

    const FULL_TEXT_NATURAL_LANGUAGE_WITH_QUERY = 2;

    const FULL_TEXT_BOOLEAN = 3;

    const FULL_TEXT_WITH_QUERY = 4;
}
