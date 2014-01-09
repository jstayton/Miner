<?php
    require_once(dirname(__FILE__) . '/../src/Miner.php');

    class MinerTest extends PHPUnit_Framework_TestCase {
        /// TODO: test other methods?

        public function testSetArray(){
            $miner = new Miner();

            $miner->setArray(array(
                'column1' => 'value1',
                'column2' => array('value' => 'value2'),
                'column3' => array('value' => 'value3', 'quote' => true),
                array('column4', 'value4', true),
                array('column5', 'value5'),
                array('column6', 'value6', false),
            ));

            $mock = $this->getMock('Miner', array('set'));

            $mock->expects($this->at(0))->method('set')
                ->with('column1', 'value1', null);
            $mock->expects($this->at(1))->method('set')
                ->with('column2', 'value2', null);
            $mock->expects($this->at(2))->method('set')
                ->with('column3', 'value3', true);
            $mock->expects($this->at(3))->method('set')
                ->with('column4', 'value4', true);
            $mock->expects($this->at(4))->method('set')
                ->with('column5', 'value5', null);
            $mock->expects($this->at(5))->method('set')
                ->with('column6', 'value6', false);

            $mock->expects($this->exactly(6))->method('set');

            $miner->mergeSetInto($mock);
        }
    }
