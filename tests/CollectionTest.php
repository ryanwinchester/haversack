<?php
/**
 * A lot of these tests come from laravel/framework collection test.
 * @see https://github.com/laravel/framework/blob/5.2/tests/Support/SupportCollectionTest.php
 * A lot of the methods are similar so I copied the code from there and modified to suit.
 */

use PHPUnit\Framework\TestCase;
use SevenShores\Haversack\Collection;

class CollectionTest extends TestCase
{
    function testCollectionIsConstructed()
    {
        $collection = new Collection('foo');
        $this->assertSame(['foo'], $collection->all());

        $collection = new Collection(2);
        $this->assertSame([2], $collection->all());

        $collection = new Collection(false);
        $this->assertSame([false], $collection->all());

        $collection = new Collection(null);
        $this->assertSame([], $collection->all());

        $collection = new Collection;
        $this->assertSame([], $collection->all());
    }

    function testReduce()
    {
        $data = new Collection([1, 2, 3]);
        $this->assertEquals(6, $data->reduce(function ($carry, $element) {
            return $carry += $element;
        }));
    }

    function testFirstReturnsFirstItemInCollection()
    {
        $c = new Collection(['foo', 'bar']);
        $this->assertEquals('foo', $c->first());
    }

    function testLastReturnsLastItemInCollection()
    {
        $c = new Collection(['foo', 'bar']);
        $this->assertEquals('bar', $c->last());
    }

    function testLastWithCallback()
    {
        $data = new Collection([2, 4, 3, 2]);
        $result = $data->last(function ($key, $value) {
            return $value > 2;
        });
        $this->assertEquals(3, $result);
    }

    function testLastWithCallbackAndDefault()
    {
        $data = new Collection(['foo', 'bar']);
        $result = $data->last(function ($key, $value) {
            return $value === 'baz';
        }, 'default');
        $this->assertEquals('default', $result);
    }

    function testLastWithDefaultAndWithoutCallback()
    {
        $data = new Collection;
        $result = $data->last(null, 'default');
        $this->assertEquals('default', $result);
    }

    function testEmptyCollectionIsEmpty()
    {
        $c = new Collection();

        $this->assertTrue($c->isEmpty());
    }

    function testOffsetAccess()
    {
        $c = new Collection(['name' => 'taylor']);
        $this->assertEquals('taylor', $c['name']);
        $c['name'] = 'dayle';
        $this->assertEquals('dayle', $c['name']);
        $this->assertTrue(isset($c['name']));
        unset($c['name']);
        $this->assertFalse(isset($c['name']));
        $c[] = 'jason';
        $this->assertEquals('jason', $c[0]);
    }

    function testArrayAccessOffsetExists()
    {
        $c = new Collection(['foo', 'bar']);
        $this->assertTrue($c->offsetExists(0));
        $this->assertTrue($c->offsetExists(1));
        $this->assertFalse($c->offsetExists(1000));
    }

    function testArrayAccessOffsetGet()
    {
        $c = new Collection(['foo', 'bar']);
        $this->assertEquals('foo', $c->offsetGet(0));
        $this->assertEquals('bar', $c->offsetGet(1));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testArrayAccessOffsetGetOnNonExist()
    {
        $c = new Collection(['foo', 'bar']);
        $c->offsetGet(1000);
    }

    function testArrayAccessOffsetSet()
    {
        $c = new Collection(['foo', 'foo']);

        $c->offsetSet(1, 'bar');
        $this->assertEquals('bar', $c[1]);

        $c->offsetSet(null, 'qux');
        $this->assertEquals('qux', $c[2]);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    function testArrayAccessOffsetUnset()
    {
        $c = new Collection(['foo', 'bar']);

        $c->offsetUnset(1);
        $c[1];
    }

    function testCountable()
    {
        $c = new Collection(['foo', 'bar']);
        $this->assertCount(2, $c);
    }

    function testIterable()
    {
        $c = new Collection(['foo']);
        $this->assertInstanceOf('ArrayIterator', $c->getIterator());
        $this->assertEquals(['foo'], $c->getIterator()->getArrayCopy());
    }

    function testCachingIterator()
    {
        $c = new Collection(['foo']);
        $this->assertInstanceOf('CachingIterator', $c->getCachingIterator());
    }

    function testFilter()
    {
        $c = new Collection([['id' => 1, 'name' => 'Hello'], ['id' => 2, 'name' => 'World']]);
        $this->assertEquals([1 => ['id' => 2, 'name' => 'World']], $c->filter(function ($item) {
            return $item['id'] == 2;
        })->all());

        $c = new Collection(['', 'Hello', '', 'World']);
        $this->assertEquals(['Hello', 'World'], $c->filter()->values()->all());

        $c = new Collection(['id' => 1, 'first' => 'Hello', 'second' => 'World']);
        $this->assertEquals(['first' => 'Hello', 'second' => 'World'], $c->filter(function ($item, $key) {
            return $key != 'id';
        })->all());
    }

    function testValues()
    {
        $c = new Collection([['id' => 1, 'name' => 'Hello'], ['id' => 2, 'name' => 'World']]);
        $this->assertEquals([['id' => 2, 'name' => 'World']], $c->filter(function ($item) {
            return $item['id'] == 2;
        })->values()->all());
    }

    function testMergeNull()
    {
        $c = new Collection(['name' => 'Hello']);
        $this->assertEquals(['name' => 'Hello'], $c->merge(null)->all());
    }

    function testMergeArray()
    {
        $c = new Collection(['name' => 'Hello']);
        $this->assertEquals(['name' => 'Hello', 'id' => 1], $c->merge(['id' => 1])->all());
    }

    function testMergeCollection()
    {
        $c = new Collection(['name' => 'Hello']);
        $this->assertEquals(['name' => 'World', 'id' => 1], $c->merge(new Collection(['name' => 'World', 'id' => 1]))->all());
    }

    function testDiffCollection()
    {
        $c = new Collection(['id' => 1, 'first_word' => 'Hello']);
        $this->assertEquals(['id' => 1], $c->diff(new Collection(['first_word' => 'Hello', 'last_word' => 'World']))->all());
    }

    function testDiffNull()
    {
        $c = new Collection(['id' => 1, 'first_word' => 'Hello']);
        $this->assertEquals(['id' => 1, 'first_word' => 'Hello'], $c->diff(null)->all());
    }

    function testDiffKeys()
    {
        $c1 = new Collection(['id' => 1, 'first_word' => 'Hello']);
        $c2 = new Collection(['id' => 123, 'foo_bar' => 'Hello']);
        $this->assertEquals(['first_word' => 'Hello'], $c1->diffKeys($c2)->all());
    }

    function testEach()
    {
        $c = new Collection($original = [1, 2, 'foo' => 'bar', 'bam' => 'baz']);

        $result = [];
        $c->each(function ($item, $key) use (&$result) {
            $result[$key] = $item;
        });
        $this->assertEquals($original, $result);

        $result = [];
        $c->each(function ($item, $key) use (&$result) {
            $result[$key] = $item;
            if (is_string($key)) {
                return false;
            }
        });
        $this->assertEquals([1, 2, 'foo' => 'bar'], $result);
    }

    function testUnique()
    {
        $c = new Collection(['Hello', 'World', 'World']);
        $this->assertEquals(['Hello', 'World'], $c->unique()->all());

        $c = new Collection([[1, 2], [1, 2], [2, 3], [3, 4], [2, 3]]);
        $this->assertEquals([[1, 2], [2, 3], [3, 4]], $c->unique()->values()->all());
    }

    //function testUniqueWithCallback()
    //{
    //    $c = new Collection([
    //        1 => ['id' => 1, 'first' => 'Taylor', 'last' => 'Otwell'], 2 => ['id' => 2, 'first' => 'Taylor', 'last' => 'Otwell'],
    //        3 => ['id' => 3, 'first' => 'Abigail', 'last' => 'Otwell'], 4 => ['id' => 4, 'first' => 'Abigail', 'last' => 'Otwell'],
    //        5 => ['id' => 5, 'first' => 'Taylor', 'last' => 'Swift'], 6 => ['id' => 6, 'first' => 'Taylor', 'last' => 'Swift'],
    //    ]);
    //
    //    $this->assertEquals([
    //        1 => ['id' => 1, 'first' => 'Taylor', 'last' => 'Otwell'],
    //        3 => ['id' => 3, 'first' => 'Abigail', 'last' => 'Otwell'],
    //    ], $c->unique('first')->all());
    //
    //    $this->assertEquals([
    //        1 => ['id' => 1, 'first' => 'Taylor', 'last' => 'Otwell'],
    //        3 => ['id' => 3, 'first' => 'Abigail', 'last' => 'Otwell'],
    //        5 => ['id' => 5, 'first' => 'Taylor', 'last' => 'Swift'],
    //    ], $c->unique(function ($item) {
    //        return $item['first'].$item['last'];
    //    })->all());
    //}

    function testSort()
    {
        $data = (new Collection([5, 3, 1, 2, 4]))->sort();
        $this->assertEquals([1, 2, 3, 4, 5], $data->values()->all());

        $data = (new Collection([-1, -3, -2, -4, -5, 0, 5, 3, 1, 2, 4]))->sort();
        $this->assertEquals([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], $data->values()->all());

        $data = (new Collection(['foo', 'bar-10', 'bar-1']))->sort();
        $this->assertEquals(['bar-1', 'bar-10', 'foo'], $data->values()->all());
    }

    //function testSortWithCallback()
    //{
    //    $data = (new Collection([5, 3, 1, 2, 4]))->sort(function ($a, $b) {
    //        if ($a === $b) {
    //            return 0;
    //        }
    //
    //        return ($a < $b) ? -1 : 1;
    //    });
    //
    //    $this->assertEquals(range(1, 5), array_values($data->all()));
    //}

    //function testSortBy()
    //{
    //    $data = new Collection(['taylor', 'dayle']);
    //    $data = $data->sortBy(function ($x) {
    //        return $x;
    //    });
    //
    //    $this->assertEquals(['dayle', 'taylor'], array_values($data->all()));
    //
    //    $data = new Collection(['dayle', 'taylor']);
    //    $data = $data->sortByDesc(function ($x) {
    //        return $x;
    //    });
    //
    //    $this->assertEquals(['taylor', 'dayle'], array_values($data->all()));
    //}

    function testSortByString()
    {
        $data = new Collection([['name' => 'taylor'], ['name' => 'dayle']]);
        $data = $data->sortBy('name');

        $this->assertEquals([['name' => 'dayle'], ['name' => 'taylor']], array_values($data->all()));
    }

    function testReverse()
    {
        $data = new Collection(['zaeed', 'alan']);
        $reversed = $data->reverse();

        $this->assertSame([1 => 'alan', 0 => 'zaeed'], $reversed->all());

        $data = new Collection(['name' => 'taylor', 'framework' => 'laravel']);
        $reversed = $data->reverse();

        $this->assertSame(['framework' => 'laravel', 'name' => 'taylor'], $reversed->all());
    }

    //function testFlip()
    //{
    //    $data = new Collection(['name' => 'taylor', 'framework' => 'laravel']);
    //    $this->assertEquals(['taylor' => 'name', 'laravel' => 'framework'], $data->flip()->toArray());
    //}

    function testChunk()
    {
        $data = new Collection([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $data = $data->chunk(3);

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertInstanceOf(Collection::class, $data[0]);
        $this->assertCount(4, $data);
        $this->assertEquals([1, 2, 3], $data[0]->toArray());
        $this->assertEquals([9 => 10], $data[3]->toArray());
    }

    function testMakeMethod()
    {
        $collection = Collection::make('foo');
        $this->assertEquals(['foo'], $collection->all());
    }

    function testMakeMethodFromNull()
    {
        $collection = Collection::make(null);
        $this->assertEquals([], $collection->all());

        $collection = Collection::make();
        $this->assertEquals([], $collection->all());
    }

    function testMakeMethodFromCollection()
    {
        $firstCollection = Collection::make(['foo' => 'bar']);
        $secondCollection = Collection::make($firstCollection);
        $this->assertEquals(['foo' => 'bar'], $secondCollection->all());
    }

    function testMakeMethodFromArray()
    {
        $collection = Collection::make(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $collection->all());
    }

    function testConstructMakeFromObject()
    {
        $object = new stdClass();
        $object->foo = 'bar';
        $collection = Collection::make($object);
        $this->assertEquals(['foo' => 'bar'], $collection->all());
    }

    function testConstructMethod()
    {
        $collection = new Collection('foo');
        $this->assertEquals(['foo'], $collection->all());
    }

    function testConstructMethodFromNull()
    {
        $collection = new Collection(null);
        $this->assertEquals([], $collection->all());

        $collection = new Collection();
        $this->assertEquals([], $collection->all());
    }

    function testConstructMethodFromCollection()
    {
        $firstCollection = new Collection(['foo' => 'bar']);
        $secondCollection = new Collection($firstCollection);
        $this->assertEquals(['foo' => 'bar'], $secondCollection->all());
    }

    function testConstructMethodFromArray()
    {
        $collection = new Collection(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $collection->all());
    }

    function testConstructMethodFromObject()
    {
        $object = new stdClass();
        $object->foo = 'bar';
        $collection = new Collection($object);
        $this->assertEquals(['foo' => 'bar'], $collection->all());
    }

    function testMap()
    {
        $data = new Collection(['first' => 'taylor', 'last' => 'otwell']);
        $data = $data->map(function ($item, $key) {
            return $key.'-'.strrev($item);
        });
        $this->assertEquals(['first' => 'first-rolyat', 'last' => 'last-llewto'], $data->all());
    }

    //function testFlatMap()
    //{
    //    $data = new Collection([
    //        ['name' => 'taylor', 'hobbies' => ['programming', 'basketball']],
    //        ['name' => 'adam', 'hobbies' => ['music', 'powerlifting']],
    //    ]);
    //    $data = $data->flatMap(function ($person) {
    //        return $person['hobbies'];
    //    });
    //    $this->assertEquals(['programming', 'basketball', 'music', 'powerlifting'], $data->all());
    //}
    //
    //function testTransform()
    //{
    //    $data = new Collection(['first' => 'taylor', 'last' => 'otwell']);
    //    $data->transform(function ($item, $key) {
    //        return $key.'-'.strrev($item);
    //    });
    //    $this->assertEquals(['first' => 'first-rolyat', 'last' => 'last-llewto'], $data->all());
    //}

    function testFirstWithCallback()
    {
        $data = new Collection(['foo', 'bar', 'baz']);
        $result = $data->first(function ($key, $value) {
            return $value === 'bar';
        });
        $this->assertEquals('bar', $result);
    }

    function testFirstWithCallbackAndDefault()
    {
        $data = new Collection(['foo', 'bar']);
        $result = $data->first(function ($key, $value) {
            return $value === 'baz';
        }, 'default');
        $this->assertEquals('default', $result);
    }

    function testFirstWithDefaultAndWithoutCallback()
    {
        $data = new Collection;
        $result = $data->first(null, 'default');
        $this->assertEquals('default', $result);
    }

    function testGettingSumFromCollection()
    {
        $c = new Collection([(object) ['foo' => 50], (object) ['foo' => 50]]);
        $this->assertEquals(100, $c->sum('foo'));

        $c = new Collection([(object) ['foo' => 50], (object) ['foo' => 50]]);
        $this->assertEquals(100, $c->sum(function ($i) {
            return $i->foo;
        }));
    }

    function testCanSumValuesWithoutACallback()
    {
        $c = new Collection([1, 2, 3, 4, 5]);
        $this->assertEquals(15, $c->sum());
    }

    function testGettingSumFromEmptyCollection()
    {
        $c = new Collection();
        $this->assertEquals(0, $c->sum('foo'));
    }

    function testGettingMaxItemsFromCollection()
    {
        $c = new Collection([(object) ['foo' => 10], (object) ['foo' => 20]]);
        $this->assertEquals(20, $c->max('foo'));

        $c = new Collection([['foo' => 10], ['foo' => 20]]);
        $this->assertEquals(20, $c->max('foo'));

        $c = new Collection([1, 2, 3, 4, 5]);
        $this->assertEquals(5, $c->max());

        $c = new Collection();
        $this->assertNull($c->max());
    }

    function testGettingMinItemsFromCollection()
    {
        $c = new Collection([(object) ['foo' => 10], (object) ['foo' => 20]]);
        $this->assertEquals(10, $c->min('foo'));

        $c = new Collection([['foo' => 10], ['foo' => 20]]);
        $this->assertEquals(10, $c->min('foo'));

        $c = new Collection([1, 2, 3, 4, 5]);
        $this->assertEquals(1, $c->min());

        $c = new Collection();
        $this->assertNull($c->min());
    }

    //function testOnly()
    //{
    //    $data = new Collection(['first' => 'Taylor', 'last' => 'Otwell', 'email' => 'taylorotwell@gmail.com']);
    //
    //    $this->assertEquals(['first' => 'Taylor'], $data->only(['first', 'missing'])->all());
    //    $this->assertEquals(['first' => 'Taylor'], $data->only('first', 'missing')->all());
    //
    //    $this->assertEquals(['first' => 'Taylor', 'email' => 'taylorotwell@gmail.com'], $data->only(['first', 'email'])->all());
    //    $this->assertEquals(['first' => 'Taylor', 'email' => 'taylorotwell@gmail.com'], $data->only('first', 'email')->all());
    //}

    //function testGettingAvgItemsFromCollection()
    //{
    //    $c = new Collection([(object) ['foo' => 10], (object) ['foo' => 20]]);
    //    $this->assertEquals(15, $c->avg('foo'));
    //
    //    $c = new Collection([['foo' => 10], ['foo' => 20]]);
    //    $this->assertEquals(15, $c->avg('foo'));
    //
    //    $c = new Collection([1, 2, 3, 4, 5]);
    //    $this->assertEquals(3, $c->avg());
    //
    //    $c = new Collection();
    //    $this->assertNull($c->avg());
    //}

    //function testJsonSerialize()
    //{
    //    $c = new Collection([
    //        new TestArrayableObject(),
    //        new TestJsonableObject(),
    //        new TestJsonSerializeObject(),
    //        'baz',
    //    ]);
    //
    //    $this->assertSame([
    //        ['foo' => 'bar'],
    //        ['foo' => 'bar'],
    //        ['foo' => 'bar'],
    //        'baz',
    //    ], $c->jsonSerialize());
    //}

    //function testCombineWithArray()
    //{
    //    $expected = [
    //        1 => 4,
    //        2 => 5,
    //        3 => 6,
    //    ];
    //
    //    $c = new Collection(array_keys($expected));
    //    $actual = $c->combine(array_values($expected))->toArray();
    //
    //    $this->assertSame($expected, $actual);
    //}
    //
    //function testCombineWithCollection()
    //{
    //    $expected = [
    //        1 => 4,
    //        2 => 5,
    //        3 => 6,
    //    ];
    //
    //    $keyCollection = new Collection(array_keys($expected));
    //    $valueCollection = new Collection(array_values($expected));
    //    $actual = $keyCollection->combine($valueCollection)->toArray();
    //
    //    $this->assertSame($expected, $actual);
    //}

    ///**
    // * @expectedException InvalidArgumentException
    // */
    //function testRandomThrowsAnExceptionUsingAmountBiggerThanCollectionSize()
    //{
    //    $data = new Collection([1, 2, 3]);
    //    $data->random(4);
    //}
    //
    //function testPipe()
    //{
    //    $collection = new Collection([1, 2, 3]);
    //
    //    $this->assertEquals(6, $collection->pipe(function ($collection) {
    //        return $collection->sum();
    //    }));
    //}
    //
    //function testMedianValueWithArrayCollection()
    //{
    //    $collection = new Collection([1, 2, 2, 4]);
    //
    //    $this->assertEquals(2, $collection->median());
    //}
    //
    //function testMedianValueByKey()
    //{
    //    $collection = new Collection([
    //        (object) ['foo' => 1],
    //        (object) ['foo' => 2],
    //        (object) ['foo' => 2],
    //        (object) ['foo' => 4],
    //    ]);
    //    $this->assertEquals(2, $collection->median('foo'));
    //}
    //
    //function testEvenMedianCollection()
    //{
    //    $collection = new Collection([
    //        (object) ['foo' => 0],
    //        (object) ['foo' => 3],
    //    ]);
    //    $this->assertEquals(1.5, $collection->median('foo'));
    //}
    //
    //function testMedianOnEmptyCollectionReturnsNull()
    //{
    //    $collection = new Collection();
    //    $this->assertNull($collection->median());
    //}
    //
    //function testModeOnNullCollection()
    //{
    //    $collection = new Collection();
    //    $this->assertNull($collection->mode());
    //}
    //
    //function testMode()
    //{
    //    $collection = new Collection([1, 2, 3, 4, 4, 5]);
    //    $this->assertEquals([4], $collection->mode());
    //}
    //
    //function testModeValueByKey()
    //{
    //    $collection = new Collection([
    //        (object) ['foo' => 1],
    //        (object) ['foo' => 1],
    //        (object) ['foo' => 2],
    //        (object) ['foo' => 4],
    //    ]);
    //    $this->assertEquals([1], $collection->mode('foo'));
    //}
    //
    //function testWithMultipleModeValues()
    //{
    //    $collection = new Collection([1, 2, 2, 1]);
    //    $this->assertEquals([1, 2], $collection->mode());
    //}

    //function testPopReturnsAndRemovesLastItemInCollection()
    //{
    //    $c = new Collection(['foo', 'bar']);
    //
    //    $this->assertEquals('bar', $c->pop());
    //    $this->assertEquals('foo', $c->first());
    //}
    //
    //function testShiftReturnsAndRemovesFirstItemInCollection()
    //{
    //    $c = new Collection(['foo', 'bar']);
    //
    //    $this->assertEquals('foo', $c->shift());
    //    $this->assertEquals('bar', $c->first());
    //}

    //function testForgetSingleKey()
    //{
    //    $c = new Collection(['foo', 'bar']);
    //    $c->forget(0);
    //    $this->assertFalse(isset($c['foo']));
    //
    //    $c = new Collection(['foo' => 'bar', 'baz' => 'qux']);
    //    $c->forget('foo');
    //    $this->assertFalse(isset($c['foo']));
    //}

    //function testForgetArrayOfKeys()
    //{
    //    $c = new Collection(['foo', 'bar', 'baz']);
    //    $c->forget([0, 2]);
    //    $this->assertFalse(isset($c[0]));
    //    $this->assertFalse(isset($c[2]));
    //    $this->assertTrue(isset($c[1]));
    //
    //    $c = new Collection(['name' => 'taylor', 'foo' => 'bar', 'baz' => 'qux']);
    //    $c->forget(['foo', 'baz']);
    //    $this->assertFalse(isset($c['foo']));
    //    $this->assertFalse(isset($c['baz']));
    //    $this->assertTrue(isset($c['name']));
    //}

    //function testWhere()
    //{
    //    $c = new Collection([['v' => 1], ['v' => 2], ['v' => 3], ['v' => '3'], ['v' => 4]]);
    //    $this->assertEquals([['v' => 3]], $c->where('v', 3)->values()->all());
    //}
    //
    //function testWhereLoose()
    //{
    //    $c = new Collection([['v' => 1], ['v' => 2], ['v' => 3], ['v' => '3'], ['v' => 4]]);
    //    $this->assertEquals([['v' => 3], ['v' => '3']], $c->whereLoose('v', 3)->values()->all());
    //}
    //
    //function testWhereIn()
    //{
    //    $c = new Collection([['v' => 1], ['v' => 2], ['v' => 3], ['v' => '3'], ['v' => 4]]);
    //    $this->assertEquals([['v' => 1], ['v' => 3]], $c->whereIn('v', [1, 3])->values()->all());
    //}
    //
    //function testWhereInLoose()
    //{
    //    $c = new Collection([['v' => 1], ['v' => 2], ['v' => 3], ['v' => '3'], ['v' => 4]]);
    //    $this->assertEquals([['v' => 1], ['v' => 3], ['v' => '3']], $c->whereInLoose('v', [1, 3])->values()->all());
    //}

    //function testFlatten()
    //{
    //    // Flat arrays are unaffected
    //    $c = new Collection(['#foo', '#bar', '#baz']);
    //    $this->assertEquals(['#foo', '#bar', '#baz'], $c->flatten()->all());
    //
    //    // Nested arrays are flattened with existing flat items
    //    $c = new Collection([['#foo', '#bar'], '#baz']);
    //    $this->assertEquals(['#foo', '#bar', '#baz'], $c->flatten()->all());
    //
    //    // Sets of nested arrays are flattened
    //    $c = new Collection([['#foo', '#bar'], ['#baz']]);
    //    $this->assertEquals(['#foo', '#bar', '#baz'], $c->flatten()->all());
    //
    //    // Deeply nested arrays are flattened
    //    $c = new Collection([['#foo', ['#bar']], ['#baz']]);
    //    $this->assertEquals(['#foo', '#bar', '#baz'], $c->flatten()->all());
    //
    //    // Nested collections are flattened alongside arrays
    //    $c = new Collection([new Collection(['#foo', '#bar']), ['#baz']]);
    //    $this->assertEquals(['#foo', '#bar', '#baz'], $c->flatten()->all());
    //
    //    // Nested collections containing plain arrays are flattened
    //    $c = new Collection([new Collection(['#foo', ['#bar']]), ['#baz']]);
    //    $this->assertEquals(['#foo', '#bar', '#baz'], $c->flatten()->all());
    //
    //    // Nested arrays containing collections are flattened
    //    $c = new Collection([['#foo', new Collection(['#bar'])], ['#baz']]);
    //    $this->assertEquals(['#foo', '#bar', '#baz'], $c->flatten()->all());
    //
    //    // Nested arrays containing collections containing arrays are flattened
    //    $c = new Collection([['#foo', new Collection(['#bar', ['#zap']])], ['#baz']]);
    //    $this->assertEquals(['#foo', '#bar', '#zap', '#baz'], $c->flatten()->all());
    //}
    //
    //function testFlattenWithDepth()
    //{
    //    // No depth flattens recursively
    //    $c = new Collection([['#foo', ['#bar', ['#baz']]], '#zap']);
    //    $this->assertEquals(['#foo', '#bar', '#baz', '#zap'], $c->flatten()->all());
    //
    //    // Specifying a depth only flattens to that depth
    //    $c = new Collection([['#foo', ['#bar', ['#baz']]], '#zap']);
    //    $this->assertEquals(['#foo', ['#bar', ['#baz']], '#zap'], $c->flatten(1)->all());
    //
    //    $c = new Collection([['#foo', ['#bar', ['#baz']]], '#zap']);
    //    $this->assertEquals(['#foo', '#bar', ['#baz'], '#zap'], $c->flatten(2)->all());
    //}

    //function testUnionNull()
    //{
    //    $c = new Collection(['name' => 'Hello']);
    //    $this->assertEquals(['name' => 'Hello'], $c->union(null)->all());
    //}
    //
    //function testUnionArray()
    //{
    //    $c = new Collection(['name' => 'Hello']);
    //    $this->assertEquals(['name' => 'Hello', 'id' => 1], $c->union(['id' => 1])->all());
    //}
    //
    //function testUnionCollection()
    //{
    //    $c = new Collection(['name' => 'Hello']);
    //    $this->assertEquals(['name' => 'Hello', 'id' => 1], $c->union(new Collection(['name' => 'World', 'id' => 1]))->all());
    //}

    //function testIntersectNull()
    //{
    //    $c = new Collection(['id' => 1, 'first_word' => 'Hello']);
    //    $this->assertEquals([], $c->intersect(null)->all());
    //}
    //
    //function testIntersectCollection()
    //{
    //    $c = new Collection(['id' => 1, 'first_word' => 'Hello']);
    //    $this->assertEquals(['first_word' => 'Hello'], $c->intersect(new Collection(['first_world' => 'Hello', 'last_word' => 'World']))->all());
    //}

    //function testCollapse()
    //{
    //    $data = new Collection([[$object1 = new StdClass], [$object2 = new StdClass]]);
    //    $this->assertEquals([$object1, $object2], $data->collapse()->all());
    //}
    //
    //function testCollapseWithNestedCollactions()
    //{
    //    $data = new Collection([new Collection([1, 2, 3]), new Collection([4, 5, 6])]);
    //    $this->assertEquals([1, 2, 3, 4, 5, 6], $data->collapse()->all());
    //}

    //function testEvery()
    //{
    //    $data = new Collection([
    //        6 => 'a',
    //        4 => 'b',
    //        7 => 'c',
    //        1 => 'd',
    //        5 => 'e',
    //        3 => 'f',
    //    ]);
    //
    //    $this->assertEquals(['a', 'e'], $data->every(4)->all());
    //    $this->assertEquals(['b', 'f'], $data->every(4, 1)->all());
    //    $this->assertEquals(['c'], $data->every(4, 2)->all());
    //    $this->assertEquals(['d'], $data->every(4, 3)->all());
    //}
    //
    //function testExcept()
    //{
    //    $data = new Collection(['first' => 'Taylor', 'last' => 'Otwell', 'email' => 'taylorotwell@gmail.com']);
    //
    //    $this->assertEquals(['first' => 'Taylor'], $data->except(['last', 'email', 'missing'])->all());
    //    $this->assertEquals(['first' => 'Taylor'], $data->except('last', 'email', 'missing')->all());
    //
    //    $this->assertEquals(['first' => 'Taylor', 'email' => 'taylorotwell@gmail.com'], $data->except(['last'])->all());
    //    $this->assertEquals(['first' => 'Taylor', 'email' => 'taylorotwell@gmail.com'], $data->except('last')->all());
    //}
    //
    //function testPluckWithArrayAndObjectValues()
    //{
    //    $data = new Collection([(object) ['name' => 'taylor', 'email' => 'foo'], ['name' => 'dayle', 'email' => 'bar']]);
    //    $this->assertEquals(['taylor' => 'foo', 'dayle' => 'bar'], $data->pluck('email', 'name')->all());
    //    $this->assertEquals(['foo', 'bar'], $data->pluck('email')->all());
    //}
    //
    //function testPluckWithArrayAccessValues()
    //{
    //    $data = new Collection([
    //        new TestArrayAccessImplementation(['name' => 'taylor', 'email' => 'foo']),
    //        new TestArrayAccessImplementation(['name' => 'dayle', 'email' => 'bar']),
    //    ]);
    //
    //    $this->assertEquals(['taylor' => 'foo', 'dayle' => 'bar'], $data->pluck('email', 'name')->all());
    //    $this->assertEquals(['foo', 'bar'], $data->pluck('email')->all());
    //}
    //
    //function testImplode()
    //{
    //    $data = new Collection([['name' => 'taylor', 'email' => 'foo'], ['name' => 'dayle', 'email' => 'bar']]);
    //    $this->assertEquals('foobar', $data->implode('email'));
    //    $this->assertEquals('foo,bar', $data->implode('email', ','));
    //
    //    $data = new Collection(['taylor', 'dayle']);
    //    $this->assertEquals('taylordayle', $data->implode(''));
    //    $this->assertEquals('taylor,dayle', $data->implode(','));
    //}
    //
    //function testTake()
    //{
    //    $data = new Collection(['taylor', 'dayle', 'shawn']);
    //    $data = $data->take(2);
    //    $this->assertEquals(['taylor', 'dayle'], $data->all());
    //}
    //
    //function testRandom()
    //{
    //    $data = new Collection([1, 2, 3, 4, 5, 6]);
    //
    //    $random = $data->random();
    //    $this->assertInternalType('integer', $random);
    //    $this->assertContains($random, $data->all());
    //
    //    $random = $data->random(3);
    //    $this->assertInstanceOf(Collection::class, $random);
    //    $this->assertCount(3, $random);
    //}
    //
    ///**
    // * @expectedException InvalidArgumentException
    // */
    //function testRandomThrowsAnErrorWhenRequestingMoreItemsThanAreAvailable()
    //{
    //    (new Collection)->random();
    //}
    //
    //function testTakeLast()
    //{
    //    $data = new Collection(['taylor', 'dayle', 'shawn']);
    //    $data = $data->take(-2);
    //    $this->assertEquals([1 => 'dayle', 2 => 'shawn'], $data->all());
    //}

    //function testMacroable()
    //{
    //    // Foo() macro : unique values starting with A
    //    Collection::macro('foo', function () {
    //        return $this->filter(function ($item) {
    //            return strpos($item, 'a') === 0;
    //        })
    //            ->unique()
    //            ->values();
    //    });
    //
    //    $c = new Collection(['a', 'a', 'aa', 'aaa', 'bar']);
    //
    //    $this->assertSame(['a', 'aa', 'aaa'], $c->foo()->all());
    //}

    //function testSplice()
    //{
    //    $data = new Collection(['foo', 'baz']);
    //    $data->splice(1);
    //    $this->assertEquals(['foo'], $data->all());
    //
    //    $data = new Collection(['foo', 'baz']);
    //    $data->splice(1, 0, 'bar');
    //    $this->assertEquals(['foo', 'bar', 'baz'], $data->all());
    //
    //    $data = new Collection(['foo', 'baz']);
    //    $data->splice(1, 1);
    //    $this->assertEquals(['foo'], $data->all());
    //
    //    $data = new Collection(['foo', 'baz']);
    //    $cut = $data->splice(1, 1, 'bar');
    //    $this->assertEquals(['foo', 'bar'], $data->all());
    //    $this->assertEquals(['baz'], $cut->all());
    //}
    //
    //function testGetPluckValueWithAccessors()
    //{
    //    $model = new TestAccessorEloquentTestStub(['some' => 'foo']);
    //    $modelTwo = new TestAccessorEloquentTestStub(['some' => 'bar']);
    //    $data = new Collection([$model, $modelTwo]);
    //
    //    $this->assertEquals(['foo', 'bar'], $data->pluck('some')->all());
    //}

    //function testGroupByAttribute()
    //{
    //    $data = new Collection([['rating' => 1, 'url' => '1'], ['rating' => 1, 'url' => '1'], ['rating' => 2, 'url' => '2']]);
    //
    //    $result = $data->groupBy('rating');
    //    $this->assertEquals([1 => [['rating' => 1, 'url' => '1'], ['rating' => 1, 'url' => '1']], 2 => [['rating' => 2, 'url' => '2']]], $result->toArray());
    //
    //    $result = $data->groupBy('url');
    //    $this->assertEquals([1 => [['rating' => 1, 'url' => '1'], ['rating' => 1, 'url' => '1']], 2 => [['rating' => 2, 'url' => '2']]], $result->toArray());
    //}
    //
    //function testGroupByAttributePreservingKeys()
    //{
    //    $data = new Collection([10 => ['rating' => 1, 'url' => '1'],  20 => ['rating' => 1, 'url' => '1'],  30 => ['rating' => 2, 'url' => '2']]);
    //
    //    $result = $data->groupBy('rating', true);
    //
    //    $expected_result = [
    //        1 => [10 => ['rating' => 1, 'url' => '1'], 20 => ['rating' => 1, 'url' => '1']],
    //        2 => [30 => ['rating' => 2, 'url' => '2']],
    //    ];
    //
    //    $this->assertEquals($expected_result, $result->toArray());
    //}
    //
    //function testGroupByClosureWhereItemsHaveSingleGroup()
    //{
    //    $data = new Collection([['rating' => 1, 'url' => '1'], ['rating' => 1, 'url' => '1'], ['rating' => 2, 'url' => '2']]);
    //
    //    $result = $data->groupBy(function ($item) {
    //        return $item['rating'];
    //    });
    //
    //    $this->assertEquals([1 => [['rating' => 1, 'url' => '1'], ['rating' => 1, 'url' => '1']], 2 => [['rating' => 2, 'url' => '2']]], $result->toArray());
    //}
    //
    //function testGroupByClosureWhereItemsHaveSingleGroupPreservingKeys()
    //{
    //    $data = new Collection([10 => ['rating' => 1, 'url' => '1'], 20 => ['rating' => 1, 'url' => '1'], 30 => ['rating' => 2, 'url' => '2']]);
    //
    //    $result = $data->groupBy(function ($item) {
    //        return $item['rating'];
    //    }, true);
    //
    //    $expected_result = [
    //        1 => [10 => ['rating' => 1, 'url' => '1'], 20 => ['rating' => 1, 'url' => '1']],
    //        2 => [30 => ['rating' => 2, 'url' => '2']],
    //    ];
    //
    //    $this->assertEquals($expected_result, $result->toArray());
    //}
    //
    //function testGroupByClosureWhereItemsHaveMultipleGroups()
    //{
    //    $data = new Collection([
    //        ['user' => 1, 'roles' => ['Role_1', 'Role_3']],
    //        ['user' => 2, 'roles' => ['Role_1', 'Role_2']],
    //        ['user' => 3, 'roles' => ['Role_1']],
    //    ]);
    //
    //    $result = $data->groupBy(function ($item) {
    //        return $item['roles'];
    //    });
    //
    //    $expected_result = [
    //        'Role_1' => [
    //            ['user' => 1, 'roles' => ['Role_1', 'Role_3']],
    //            ['user' => 2, 'roles' => ['Role_1', 'Role_2']],
    //            ['user' => 3, 'roles' => ['Role_1']],
    //        ],
    //        'Role_2' => [
    //            ['user' => 2, 'roles' => ['Role_1', 'Role_2']],
    //        ],
    //        'Role_3' => [
    //            ['user' => 1, 'roles' => ['Role_1', 'Role_3']],
    //        ],
    //    ];
    //
    //    $this->assertEquals($expected_result, $result->toArray());
    //}
    //
    //function testGroupByClosureWhereItemsHaveMultipleGroupsPreservingKeys()
    //{
    //    $data = new Collection([
    //        10 => ['user' => 1, 'roles' => ['Role_1', 'Role_3']],
    //        20 => ['user' => 2, 'roles' => ['Role_1', 'Role_2']],
    //        30 => ['user' => 3, 'roles' => ['Role_1']],
    //    ]);
    //
    //    $result = $data->groupBy(function ($item) {
    //        return $item['roles'];
    //    }, true);
    //
    //    $expected_result = [
    //        'Role_1' => [
    //            10 => ['user' => 1, 'roles' => ['Role_1', 'Role_3']],
    //            20 => ['user' => 2, 'roles' => ['Role_1', 'Role_2']],
    //            30 => ['user' => 3, 'roles' => ['Role_1']],
    //        ],
    //        'Role_2' => [
    //            20 => ['user' => 2, 'roles' => ['Role_1', 'Role_2']],
    //        ],
    //        'Role_3' => [
    //            10 => ['user' => 1, 'roles' => ['Role_1', 'Role_3']],
    //        ],
    //    ];
    //
    //    $this->assertEquals($expected_result, $result->toArray());
    //}
    //
    //function testKeyByAttribute()
    //{
    //    $data = new Collection([['rating' => 1, 'name' => '1'], ['rating' => 2, 'name' => '2'], ['rating' => 3, 'name' => '3']]);
    //
    //    $result = $data->keyBy('rating');
    //    $this->assertEquals([1 => ['rating' => 1, 'name' => '1'], 2 => ['rating' => 2, 'name' => '2'], 3 => ['rating' => 3, 'name' => '3']], $result->all());
    //
    //    $result = $data->keyBy(function ($item) {
    //        return $item['rating'] * 2;
    //    });
    //    $this->assertEquals([2 => ['rating' => 1, 'name' => '1'], 4 => ['rating' => 2, 'name' => '2'], 6 => ['rating' => 3, 'name' => '3']], $result->all());
    //}
    //
    //function testKeyByClosure()
    //{
    //    $data = new Collection([
    //        ['firstname' => 'Taylor', 'lastname' => 'Otwell', 'locale' => 'US'],
    //        ['firstname' => 'Lucas', 'lastname' => 'Michot', 'locale' => 'FR'],
    //    ]);
    //    $result = $data->keyBy(function ($item, $key) {
    //        return strtolower($key.'-'.$item['firstname'].$item['lastname']);
    //    });
    //    $this->assertEquals([
    //        '0-taylorotwell' => ['firstname' => 'Taylor', 'lastname' => 'Otwell', 'locale' => 'US'],
    //        '1-lucasmichot'  => ['firstname' => 'Lucas', 'lastname' => 'Michot', 'locale' => 'FR'],
    //    ], $result->all());
    //}
    //
    //function testContains()
    //{
    //    $c = new Collection([1, 3, 5]);
    //
    //    $this->assertTrue($c->contains(1));
    //    $this->assertFalse($c->contains(2));
    //    $this->assertTrue($c->contains(function ($value) {
    //        return $value < 5;
    //    }));
    //    $this->assertFalse($c->contains(function ($value) {
    //        return $value > 5;
    //    }));
    //
    //    $c = new Collection([['v' => 1], ['v' => 3], ['v' => 5]]);
    //
    //    $this->assertTrue($c->contains('v', 1));
    //    $this->assertFalse($c->contains('v', 2));
    //
    //    $c = new Collection(['date', 'class', (object) ['foo' => 50]]);
    //
    //    $this->assertTrue($c->contains('date'));
    //    $this->assertTrue($c->contains('class'));
    //    $this->assertFalse($c->contains('foo'));
    //}

    //function testValueRetrieverAcceptsDotNotation()
    //{
    //    $c = new Collection([
    //        (object) ['id' => 1, 'foo' => ['bar' => 'B']], (object) ['id' => 2, 'foo' => ['bar' => 'A']],
    //    ]);
    //
    //    $c = $c->sortBy('foo.bar');
    //    $this->assertEquals([2, 1], $c->pluck('id')->all());
    //}
    //
    //function testPullRetrievesItemFromCollection()
    //{
    //    $c = new Collection(['foo', 'bar']);
    //
    //    $this->assertEquals('foo', $c->pull(0));
    //}
    //
    //function testPullRemovesItemFromCollection()
    //{
    //    $c = new Collection(['foo', 'bar']);
    //    $c->pull(0);
    //    $this->assertEquals([1 => 'bar'], $c->all());
    //}
    //
    //function testPullReturnsDefault()
    //{
    //    $c = new Collection([]);
    //    $value = $c->pull(0, 'foo');
    //    $this->assertEquals('foo', $value);
    //}
    //
    //function testRejectRemovesElementsPassingTruthTest()
    //{
    //    $c = new Collection(['foo', 'bar']);
    //    $this->assertEquals(['foo'], $c->reject('bar')->values()->all());
    //
    //    $c = new Collection(['foo', 'bar']);
    //    $this->assertEquals(['foo'], $c->reject(function ($v) {
    //        return $v == 'bar';
    //    })->values()->all());
    //
    //    $c = new Collection(['foo', null]);
    //    $this->assertEquals(['foo'], $c->reject(null)->values()->all());
    //
    //    $c = new Collection(['foo', 'bar']);
    //    $this->assertEquals(['foo', 'bar'], $c->reject('baz')->values()->all());
    //
    //    $c = new Collection(['foo', 'bar']);
    //    $this->assertEquals(['foo', 'bar'], $c->reject(function ($v) {
    //        return $v == 'baz';
    //    })->values()->all());
    //
    //    $c = new Collection(['id' => 1, 'primary' => 'foo', 'secondary' => 'bar']);
    //    $this->assertEquals(['primary' => 'foo', 'secondary' => 'bar'], $c->reject(function ($item, $key) {
    //        return $key == 'id';
    //    })->all());
    //}
    //
    //function testSearchReturnsIndexOfFirstFoundItem()
    //{
    //    $c = new Collection([1, 2, 3, 4, 5, 2, 5, 'foo' => 'bar']);
    //
    //    $this->assertEquals(1, $c->search(2));
    //    $this->assertEquals('foo', $c->search('bar'));
    //    $this->assertEquals(4, $c->search(function ($value) {
    //        return $value > 4;
    //    }));
    //    $this->assertEquals('foo', $c->search(function ($value) {
    //        return ! is_numeric($value);
    //    }));
    //}
    //
    //function testSearchReturnsFalseWhenItemIsNotFound()
    //{
    //    $c = new Collection([1, 2, 3, 4, 5, 'foo' => 'bar']);
    //
    //    $this->assertFalse($c->search(6));
    //    $this->assertFalse($c->search('foo'));
    //    $this->assertFalse($c->search(function ($value) {
    //        return $value < 1 && is_numeric($value);
    //    }));
    //    $this->assertFalse($c->search(function ($value) {
    //        return $value == 'nope';
    //    }));
    //}
    //
    //function testKeys()
    //{
    //    $c = new Collection(['name' => 'taylor', 'framework' => 'laravel']);
    //    $this->assertEquals(['name', 'framework'], $c->keys()->all());
    //}
    //
    //function testPaginate()
    //{
    //    $c = new Collection(['one', 'two', 'three', 'four']);
    //    $this->assertEquals(['one', 'two'], $c->forPage(1, 2)->all());
    //    $this->assertEquals([2 => 'three', 3 => 'four'], $c->forPage(2, 2)->all());
    //    $this->assertEquals([], $c->forPage(3, 2)->all());
    //}
    //
    //function testPrepend()
    //{
    //    $c = new Collection(['one', 'two', 'three', 'four']);
    //    $this->assertEquals(['zero', 'one', 'two', 'three', 'four'], $c->prepend('zero')->all());
    //
    //    $c = new Collection(['one' => 1, 'two' => 2]);
    //    $this->assertEquals(['zero' => 0, 'one' => 1, 'two' => 2], $c->prepend(0, 'zero')->all());
    //}
    //
    //function testZip()
    //{
    //    $c = new Collection([1, 2, 3]);
    //    $c = $c->zip(new Collection([4, 5, 6]));
    //    $this->assertInstanceOf(Collection::class, $c);
    //    $this->assertInstanceOf(Collection::class, $c[0]);
    //    $this->assertInstanceOf(Collection::class, $c[1]);
    //    $this->assertInstanceOf(Collection::class, $c[2]);
    //    $this->assertCount(3, $c);
    //    $this->assertEquals([1, 4], $c[0]->all());
    //    $this->assertEquals([2, 5], $c[1]->all());
    //    $this->assertEquals([3, 6], $c[2]->all());
    //
    //    $c = new Collection([1, 2, 3]);
    //    $c = $c->zip([4, 5, 6], [7, 8, 9]);
    //    $this->assertCount(3, $c);
    //    $this->assertEquals([1, 4, 7], $c[0]->all());
    //    $this->assertEquals([2, 5, 8], $c[1]->all());
    //    $this->assertEquals([3, 6, 9], $c[2]->all());
    //
    //    $c = new Collection([1, 2, 3]);
    //    $c = $c->zip([4, 5, 6], [7]);
    //    $this->assertCount(3, $c);
    //    $this->assertEquals([1, 4, 7], $c[0]->all());
    //    $this->assertEquals([2, 5, null], $c[1]->all());
    //    $this->assertEquals([3, 6, null], $c[2]->all());
    //}
}