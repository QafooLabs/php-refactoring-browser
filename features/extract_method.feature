Feature: Extract Method
    In order to extract a list of statements into its own method
    As a developer
    I need an extract method refactoring

    Scenario: "Extract side effect free line into method"
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            class Foo
            {
                public function operation()
                {
                    echo "Hello World";
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | range     | 6-6         |
            | newmethod | hello       |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -3,6 +3,11 @@
             {
                 public function operation()
                 {
            +        $this->hello();
            +    }
            +
            +    private function hello()
            +    {
                     echo "Hello World";
                 }
             }
            """

    Scenario: "Extract side effect free line from static method"
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            class Foo
            {
                public static function operation()
                {
                    echo "Hello World";
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | range     | 6-6         |
            | newmethod | hello       |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -3,6 +3,11 @@
             {
                 public static function operation()
                 {
            +        self::hello();
            +    }
            +
            +    private static function hello()
            +    {
                     echo "Hello World";
                 }
             }
            """

    Scenario: "Extract Method with instance variable"
        Given a PHP File named "src/WithInstance.php" with:
            """
            <?php
            class WithInstance
            {
                private $hello = 'Hello World!';
                public function test()
                {
                    echo $this->hello;
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                |
            | file      | src/WithInstance.php |
            | range     | 7-7                  |
            | newmethod | printHello           |
        Then the PHP File "src/WithInstance.php" should be refactored:
            """
            --- a/vfs://project/src/WithInstance.php
            +++ b/vfs://project/src/WithInstance.php
            @@ -4,6 +4,11 @@
                 private $hello = 'Hello World!';
                 public function test()
                 {
            +        $this->printHello();
            +    }
            +
            +    private function printHello()
            +    {
                     echo $this->hello;
                 }
             }
            """

    Scenario: "Extract Method with local variable"
        Given a PHP File named "src/WithLocal.php" with:
            """
            <?php
            class WithLocal
            {
                public function test()
                {
                    $hello = 'Hello World!';
                    echo $hello;
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                |
            | file      | src/WithLocal.php |
            | range     | 7-7                  |
            | newmethod | printHello           |
        Then the PHP File "src/WithLocal.php" should be refactored:
            """
            --- a/vfs://project/src/WithLocal.php
            +++ b/vfs://project/src/WithLocal.php
            @@ -4,6 +4,11 @@
                 public function test()
                 {
                     $hello = 'Hello World!';
            +        $this->printHello($hello);
            +    }
            +
            +    private function printHello($hello)
            +    {
                     echo $hello;
                 }
             }
            """

    Scenario: "Extract Method with formatting that requires line range correction"
        Given a PHP File named "src/MultiLineCorrection.php" with:
            """
            <?php
            class MultiLineCorrection
            {
                public function test()
                {
                    foo(
                        "bar"
                    );
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                       |
            | file      | src/MultiLineCorrection.php |
            | range     | 6-8                         |
            | newmethod | foo                         |
        Then the PHP File "src/MultiLineCorrection.php" should be refactored:
            """
            --- a/vfs://project/src/MultiLineCorrection.php
            +++ b/vfs://project/src/MultiLineCorrection.php
            @@ -2,6 +2,11 @@
             class MultiLineCorrection
             {
                 public function test()
            +    {
            +        $this->foo();
            +    }
            +
            +    private function foo()
                 {
                     foo(
                         "bar"
            """

    Scenario: "Extract Method with one assignment returns value"
        Given a PHP File named "src/Assignment.php" with:
            """
            <?php
            class Assignment
            {
                public function test()
                {
                    $var = "foo";
                    echo $var;
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                       |
            | file      | src/Assignment.php |
            | range     | 6-6                         |
            | newmethod | foo                         |
        Then the PHP File "src/Assignment.php" should be refactored:
            """
            --- a/vfs://project/src/Assignment.php
            +++ b/vfs://project/src/Assignment.php
            @@ -3,7 +3,14 @@
             {
                 public function test()
                 {
            -        $var = "foo";
            +        $var = $this->foo();
                     echo $var;
                 }
            +
            +    private function foo()
            +    {
            +        $var = "foo";
            +
            +        return $var;
            +    }
             }
            """

    Scenario: Extract method with multiple assignments and only one reused variable
        Given a PHP File named "src/MultiAssignmentSingleReturn.php" with:
            """
            <?php
            class MultiAssignment
            {
                public function test()
                {
                    $var = 'foo';
                    $var2 = $var;

                    echo $var2;
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                       |
            | file      | src/MultiAssignmentSingleReturn.php |
            | range     | 6-7                         |
            | newmethod | foo                         |
        Then the PHP File "src/MultiAssignmentSingleReturn.php" should be refactored:
            """
            --- a/vfs://project/src/MultiAssignmentSingleReturn.php
            +++ b/vfs://project/src/MultiAssignmentSingleReturn.php
            @@ -3,9 +3,16 @@
             {
                 public function test()
                 {
            +        $var2 = $this->foo();
            +
            +        echo $var2;
            +    }
            +
            +    private function foo()
            +    {
                     $var = 'foo';
                     $var2 = $var;
             
            -        echo $var2;
            +        return $var2;
                 }
             }
            """

    Scenario: Extract method from inside a block
        Given a PHP File named "src/ExtractMethodFromBlock.php" with:
            """
            <?php
            class ExtractMethodFromBlock
            {
                public function operation()
                {
                    for ($i=0; $i<5; $i++) {
                        echo "Hello World";
                    }
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value       |
            | file      | src/ExtractMethodFromBlock.php |
            | range     | 7-7         |
            | newmethod | hello       |
        Then the PHP File "src/ExtractMethodFromBlock.php" should be refactored:
            """
            --- a/vfs://project/src/ExtractMethodFromBlock.php
            +++ b/vfs://project/src/ExtractMethodFromBlock.php
            @@ -4,7 +4,12 @@
                 public function operation()
                 {
                     for ($i=0; $i<5; $i++) {
            -            echo "Hello World";
            +            $this->hello();
                     }
                 }
            +
            +    private function hello()
            +    {
            +        echo "Hello World";
            +    }
             }

            """
