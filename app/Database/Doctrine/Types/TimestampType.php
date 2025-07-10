<?php

namespace App\Database\Doctrine\Types;

use Illuminate\Database\DBAL\TimestampType as DBALTimestampType;

class TimestampType extends DBALTimestampType
{
    public function getName()
    {
        return 'timestamp';
    }
}
