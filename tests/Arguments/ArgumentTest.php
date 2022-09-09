<?php

namespace Test\Arguments;
use PHPUnit\Framework\TestCase;
use App\Blog\Arguments\Argument;
use App\Exceptions\ArgumentException;

class ArgumentTest extends TestCase
{
  public function testItReturnsArgumentsValueByName() : void
  {
    // Подготовка
    $arguments = new Argument(['some_key' => 'some_value']);
    // Действие
    $value = $arguments->get('some_key');
    // Проверка
    $this->assertEquals('some_value', $value);
  }

  public function argumentsProvider(): iterable
  {
    return [
    ['some_string', 'some_string'],
    [' some_string', 'some_string'],
    [' some_string ', 'some_string'],
    [123, '123'],
    [12.3, '12.3'],
    ];
  }

/**
    * @dataProvider argumentsProvider
*/
    public function testItConvertsArgumentsToStrings ($inputValue, $expectedValue): void
    {
        $arguments = new Argument(['some_key' => $inputValue]);
        $value = $arguments->get('some_key');
        $this->assertEquals($expectedValue, $value);
    }

  public function testItThrowsAnExceptionWhenArgumentIsAbsent(): void
    {
    // Подготовка
    $arguments = new Argument([]);
    // Описываем тип ожидаемого исключения
    $this->expectException(ArgumentException::class);
    // и его сообщение
    $this->expectExceptionMessage("No such argument: some_key");
    // Действие, ведущее к исключению
    $arguments->get('some_key');
    }
}