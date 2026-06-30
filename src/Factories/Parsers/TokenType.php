<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers;

enum TokenType
{
	case OPEN_PAREN;
	case CLOSE_PAREN;
	case COMMA;
	case AND_OP;
	case OR_OP;
	case IS_NOT_NULL;
	case IS_NULL;
	case NOT_IN;
	case NOT_LIKE;
	case IN_OP;
	case LIKE_OP;
	case GEQ;
	case LEQ;
	case NEQ;
	case GT;
	case LT;
	case EQ;
	case PARAM;
	case STRING_VALUE;
	case NUMBER_VALUE;
	case COLUMN;
}
