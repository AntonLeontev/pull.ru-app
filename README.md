# Соответствие сущностей в системах

| App | InSales | Мой Склад |
| ------ | ------ | ------ |
| Category | [Collection](https://api.insales.ru/#collection) | [Группа товаров](https://dev.moysklad.ru/doc/api/remap/1.2/dictionaries/#suschnosti-gruppa-towarow) |
| Product | [Product](https://api.insales.ru/#product) | [Товар](https://dev.moysklad.ru/doc/api/remap/1.2/dictionaries/#suschnosti-towar) |
| Variant | [Variant](https://api.insales.ru/#variant) | [Модификация](https://dev.moysklad.ru/doc/api/remap/1.2/dictionaries/#suschnosti-modifikaciq) |
| Option | [Option](https://api.insales.ru/#option-name) | [Характеристика](https://dev.moysklad.ru/doc/api/remap/1.2/dictionaries/#suschnosti-harakteristiki-modifikacij) |

# Соответствие статусов заказа
| App | InSales | Cdek FF | Мой Склад |
| ------ | ------ | ------ | ------ |
| init | new | - | - |
| approved | approved | pending | 400c639b-ad4b-11ee-0a80-0dfd005ae9c3 (Подтвержден) |
| dispatched | dispatched | delivery | 400c6431-ad4b-11ee-0a80-0dfd005ae9c5 (Отгружен) |
| delivered | delivered | complete | 400c64b6-ad4b-11ee-0a80-0dfd005ae9c6 (Доставлен) |
| canceled | declined | cancel | 400c6545-ad4b-11ee-0a80-0dfd005ae9c8 (Отменен) |
| returned | returned | return | 400c6500-ad4b-11ee-0a80-0dfd005ae9c7 (Возврат) |
