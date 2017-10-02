<?php

trait HelloWorld {
    public function sayHello() {
         echo 'Hello World!';
    }
}

//Change visibility of sayHello
class MyClass1 {
    use HelloWorld {
        sayHello as protected;
    }
}

class MyClass2 {
    use HelloWorld {
        sayHello as private MyPrivateHello;
    }
}

$class1 = new MyClass1();
$class1->sayHello();
$class2 = new MyClass2();
$class2->MyPrivateHello();

?>
