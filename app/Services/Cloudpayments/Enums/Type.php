<?php

namespace App\Services\Cloudpayments\Enums;

enum Type: string
{
    /**
     * Приход Выдается при получении средств от покупателя (клиента)
     */
    case income = 'Income';
    /**
     * Возврат прихода Выдается при возврате покупателю (клиенту) средств, полученных от него
     */
    case incomeReturn = 'IncomeReturn';
    /**
     * Расход Выдается при выдаче средств покупателю (клиенту)
     */
    case expense = 'Expense';
    /**
     * Возврат расхода Выдается при получении средств от покупателя (клиента), выданных ему
     */
    case expenseReturn = 'ExpenseReturn';
}
