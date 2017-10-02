<?php

trait Counter {
    static $c = 0;
    public function inc() {
        self::$c = self::$c + 1;
        echo self::$c."\n";
    }
}

class C1 {
    use Counter;
}

class C2 {
    use Counter;
}
$o = new C1();
$o->inc();
$o->inc();
$p = new C2();
$p->inc();
$p->inc();

?>
