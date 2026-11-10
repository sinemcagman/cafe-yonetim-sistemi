<?php

/*
 * Şube bilgileri: adisyon ve fiş çıktılarının başlığında ve altında kullanılır.
 */
return [

    'name' => env('KAFE_NAME', env('APP_NAME', 'Demlio')),

    'address' => env('KAFE_ADDRESS', ''),

    'phone' => env('KAFE_PHONE', ''),

    // Adisyonun en altında yazan teşekkür notu
    'receipt_footer' => env('KAFE_RECEIPT_FOOTER', 'Bizi tercih ettiğiniz için teşekkürler, afiyet olsun!'),

];
