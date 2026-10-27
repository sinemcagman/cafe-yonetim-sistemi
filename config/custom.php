<?php

// Vuexy arayüz ayarları
// -------------------------------------------------------------------------------------
// ÖNEMLİ: Buradaki değişiklikleri görmek için tarayıcının yerel depolamasını (local storage)
// ve "admin-mode", "LayoutCollapsed" gibi çerezleri temizlemeniz gerekebilir.

return [
  'custom' => [
    'myLayout' => 'vertical', // Layout type: 'vertical' (default), 'horizontal'
    // 'primaryColor' => '#FF4646', // Set the primary color
    'myTheme' => 'light', // Theme options: 'light' (default), 'dark', 'system'
    'mySkins' => 'default', // Skin options: 'default', 'bordered'
    'hasSemiDark' => false, // Semi-dark mode: true/false (false by default)
    'myRTLMode' => false, // Right-to-left (RTL) layout: true/false (false by default)
    'hasCustomizer' => true, // Tema (açık/koyu) değiştirici için açık kalmalı
    'displayCustomizer' => false, // Sağdaki ayar panelini çalışanlara gösterme
    'contentLayout' => 'wide', // Masa ekranı geniş alanı iyi kullansın diye: 'wide' (container-fluid)
    'navbarType' => 'sticky', // Navbar type: 'sticky', 'static', or 'hidden' (only for vertical layout)
    'footerFixed' => false, // Footer fixed position: true/false (false by default)
    'menuFixed' => true, // Menu fixed position: true (default) or false (only for vertical layout)
    'menuCollapsed' => false, // Menu collapsed state: true/false (false by default)
    'headerType' => 'fixed', // Header type: 'static' or 'fixed' (only for horizontal layout)
    'showDropdownOnHover' => true, // Dropdown on hover for horizontal layout: true/false
    'customizerControls' => [
      'color',
      'theme',
      'skins',
      'semiDark',
      'layoutCollapsed',
      'layoutNavbarOptions',
      'headerType',
      'contentLayout',
      'rtl'
    ],
  ],
];
