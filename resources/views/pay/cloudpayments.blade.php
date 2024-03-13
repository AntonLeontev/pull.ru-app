<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата заказа</title>
    <script src="https://widget.cloudpayments.ru/bundles/cloudpayments.js"></script>
</head>

<body>
    <script defer>
        this.pay = function() {
            let widget = new cp.CloudPayments({
                yandexPaySupport: false,
                applePaySupport: false,
                googlePaySupport: false,
                masterPassSupport: false,
                tinkoffInstallmentSupport: false
            });

			let order = @json($order);

            let receipt = {
                'Items': [],
                'calculationPlace': 'limmite.ru', //место осуществления расчёта, по умолчанию берется значение из кассы
                'phone': order.client.phone, //телефон покупателя в любом формате, если нужно отправить сообщение со ссылкой на чек
                'isBso': false, //чек является бланком строгой отчётности
                'amounts': {
                    'electronic': order.total_price, // Сумма оплаты электронными деньгами
                }
            };

            order.order_lines.forEach(line => {
                receipt['Items'].push({
                    'label': line.title, //наименование товара
                    'price': line.full_sale_price, //цена
                    'quantity': line.quantity, //количество
                    'amount': line.full_total_price, //сумма
                    'vat': null, //ставка НДС
                    'method': 1, // тег-1214 признак способа расчета - признак способа расчета
                    'object': 1, // тег-1212 признак предмета расчета - признак предмета товара, работы, услуги, платежа, выплаты, иного предмета расчета
                    'measurementUnit': 'шт', //единица измерения
                    'AgentSign': 6, //признак агента, тег ОФД 1057, 1222
                    'PurveyorData': { //данные поставщика платежного агента,  тег ОФД 1224
                        // 'Phone': '+74951234567', // телефон поставщика, тег ОД 1171
                        'Name': this.productsOrganizations[line.product_id].title, // наименование поставщика, тег ОФД 1225
                        'Inn': this.productsOrganizations[line.product_id].inn // ИНН поставщика, тег ОФД 1226
                    }
                })
            })

            if (order.full_delivery_price > 0) {
                receipt['Items'].push({
                    'label': 'Доставка', //наименование товара
                    'price': order.full_delivery_price, //цена
                    'quantity': 1, //количество
                    'amount': order.full_delivery_price, //сумма
                    'vat': null, //ставка НДС
                    'method': 1, // тег-1214 признак способа расчета - признак способа расчета
                    'object': 4, // тег-1212 признак предмета расчета - признак предмета товара, работы, услуги, платежа, выплаты, иного предмета расчета
                })
            }

            widget.pay('charge', {
                publicId: 'pk_c99424b82aed407cd4b167d280b77',
                // publicId: 'pk_211facd05c03ac924464a0ff67758',
                description: 'Оплата товаров в limmite.ru',
                amount: order.total_price,
                currency: 'RUB',
                invoiceId: order.number,
                email: order.client.email,
                skin: 'classic',
                data: {
                    CloudPayments: {
                        CustomerReceipt: receipt
                    }
                }
            }, {
                onSuccess: (options) => { // success
                    location.href = '/page/order-success'
                },
                onFail: (reason, options) => { // fail
                    //действие при неуспешной оплате
                },
                onComplete: (paymentResult,
                options) => { //Вызывается как только виджет получает от api.cloudpayments ответ с результатом транзакции.
                    //например вызов вашей аналитики Facebook Pixel
                }
            })
        };

        pay();
    </script>
</body>

</html>
