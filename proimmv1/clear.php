<?php
// clear.php - À placer dans /home/ryapo/public_html/
$composerPath = '/home/ryapo/dev/composer.phar'; // Chemin absolu vers composer.phar

echo "🧹 Nettoyage des caches Laravel...\n\n";
echo "<pre>";
echo shell_exec('php artisan config:clear 2>&1');
echo shell_exec('php artisan cache:clear 2>&1');
echo shell_exec('php artisan route:clear 2>&1');
echo shell_exec('php artisan view:clear 2>&1');
echo shell_exec('php artisan optimize:clear 2>&1');

echo "\n🔄 Régénération de l'autoloader...\n\n";

if (file_exists($composerPath)) {
    echo "✅ Composer trouvé : " . $composerPath . "\n";
    echo "🔧 Version : " . shell_exec("php " . escapeshellarg($composerPath) . " --version 2>&1");
    echo "\n📦 Exécution de dump-autoload :\n";
    echo shell_exec("php " . escapeshellarg($composerPath) . " dump-autoload 2>&1");
} else {
    echo "❌ Composer non trouvé à : " . $composerPath . "\n";
}

echo "\n✅ Terminé !\n";
echo "</pre>";






/**

 *
 * Installation du. composer
 */


// get-composer.php - À placer dans /home/ryapo/public_html/
//$target = __DIR__ . '/composer.phar';
//
//echo "📂 Dossier actuel : " . __DIR__ . "<br><br>";
//
//if (!file_exists($target)) {
//    echo "📥 Téléchargement de composer.phar...<br>";
//    $content = @file_get_contents('https://getcomposer.org/composer.phar');
//
//    if ($content) {
//        file_put_contents($target, $content);
//        echo "✅ composer.phar téléchargé avec succès !<br>";
//        echo "📦 Taille : " . filesize($target) . " octets<br>";
//    } else {
//        echo "❌ Échec du téléchargement. Vérifiez que allow_url_fopen est activé<br>";
//    }
//} else {
//    echo "✅ composer.phar existe déjà<br>";
//}
//
//if (file_exists($target)) {
//    echo "<br>🔧 Test de Composer :<br>";
//    echo "<pre>";
//    echo shell_exec("php " . escapeshellarg($target) . " --version 2>&1");
//    echo "</pre>";
//}
//
//echo "<br>✅ Terminé !<br>";
//echo "<a href='clear.php'>Retour à clear.php</a>";