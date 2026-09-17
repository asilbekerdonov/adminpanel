<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Бизнес-ошибка оргструктуры: сущность нельзя удалить/изменить
 * (есть дочерние элементы, попытка удалить свою должность и т.п.).
 */
class StructureNotEmptyException extends RuntimeException {}
