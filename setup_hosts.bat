@echo off
:: Check for administrative privileges
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Yonetici yetkileri onaylandi. hosts dosyasi guncelleniyor...
    powershell -Command "Add-Content -Path C:\Windows\System32\drivers\etc\hosts -Value '`n104.247.162.226 bybossmimarlik.com`n104.247.162.226 www.bybossmimarlik.com' -ErrorAction Stop"
    echo.
    echo hosts dosyasi basariyla guncellendi!
    echo Tarayicinizi kapatip actiktan sonra bybossmimarlik.com adresine baglanabilirsiniz.
    echo.
    pause
) else (
    echo HATA: Bu dosyayi YONETICI olarak calistirmeniz gerekmektedir.
    echo Lutfen dosyaya sag tiklayip 'Yonetici olarak calistir' secenegini secin.
    echo.
    pause
)
