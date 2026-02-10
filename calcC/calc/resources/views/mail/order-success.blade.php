<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ #{{ $order->id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<style>
  * {
    font-family: inherit;
  }

  body {
    font-family: "Roboto", sans-serif;
  }

  .mb-2 {
    margin-bottom: 10px;
  }

  .leading-1\.5 {
    line-height: 1.5;
  }
</style>
</head>
<body style="background-color:#f3f4f6; margin:0; padding:0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; min-height:100vh; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 840px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 32px;">
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <div style="font-size: 32px; font-weight: 700; color: #009966; margin-bottom: 8px;">{{ config('app.name') }}</div>
                            <div style="font-size: 18px; color: #374151;">Подтверждение заказа</div>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="font-size: 16px; color: #374151; padding-bottom: 14px;">
                            Здравствуйте, <span style="font-weight:600;">{{ $order->user->name }}</span>!<br><br>
                        </td>
                    </tr>

                    <tr>
                      <td class="leading-1.5" style="padding-bottom: 14px;">
                        <p>
													Ваш заказ успешно оформлен и принят! Спасибо, что выбрали нашу компанию!
                          <br />
                          Если у вас возникнут вопросы, пожалуйста, не стесняйтесь обращаться к нам.
                          <br />
                          Спасибо за выбор нашей компании. <br> Данные водителя отправляются на указанную в контрагенте электронную почту в день выгрузки.
                        </p>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>
                          <h3>Детали заказа:</h3>
                          <ul>
                              <li class="mb-2 leading-1.5"><strong>Номер заказа:</strong> #{{ $order->id }} </li>
                              <li class="mb-2 leading-1.5"><strong>Отправление:</strong> {{ \Illuminate\Support\Carbon::parse($order->post_date)->format('d.m.Y') }} <br /> {{ $order->warehouse_id }} </li>
                              <li class="mb-2 leading-1.5"><strong>Прибытие:</strong> {{ \Illuminate\Support\Carbon::parse($order->delivery_date)->format('d.m.Y') }} <br /> {{ $order->distributor_id }} {{ $order->distributor_center_id }} </li>
                              <li class="mb-2 leading-1.5"><strong>Дата заказа:</strong> {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d.m.Y') }}</li>
                              <li class="mb-2 leading-1.5"><strong>Cпособ оплаты:</strong> {{ $order->getPaymentMethodLabel() }}</li>
                              <li class="mb-2 leading-1.5"><strong>Сумма:</strong> {{ $order->total }}</li>
                          </ul>
                      </td>
                    </tr>
                    {{-- <tr>
                        <td style="font-size: 14px; color: #6b7280; padding-bottom: 16px;">
                            Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.
                        </td>
                    </tr> --}}

                    <tr>
                        <td align="center" style="padding-bottom: 32px;">
                            <a href="{{ url('/history') }}" class="mb-2" style="display:inline-block; background-color:#009966; color:#fff; font-weight:600; padding:14px 32px; border-radius:8px; text-decoration:none; font-size:16px; box-shadow:0 1px 3px rgba(37,99,235,0.15);">
                              Посмотреть историю
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #6b7280;" class="leading-1.5">
                            Это письмо сгенерировано автоматически, пожалуйста, не отвечайте на него.
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #6b7280;" class="leading-1.5">
                            Спасибо, <span style="color:#009966;">{{ config('app.name') }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>