<?php

namespace QafooLabs\Refactoring\Services\Diffs;

class DiffBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyFileAppendLine()
    {
        $builder = new DiffBuilder('');
        $builder->appendToLine(1, 'foo');

        $this->assertEquals(<<<DIFF
@@ -0,0 +1,1 @@
+foo
DIFF
            , $builder->generateUnifiedDiff());
    }
}
