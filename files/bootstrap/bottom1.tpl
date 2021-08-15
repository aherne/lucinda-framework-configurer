if (empty($argv)) {
    $object = new Lucinda\STDOUT\FrontController("stdout.xml", new Lucinda\Project\Attributes());
(EVENTS)
    $object->run();
} else {
    $object = new Lucinda\ConsoleSTDOUT\FrontController("stdout.xml", new Lucinda\ConsoleSTDOUT\Attributes());
    $object->run();
}