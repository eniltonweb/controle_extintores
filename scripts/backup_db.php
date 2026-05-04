<?php
$backup_file = '../backups/eniltonbd_backup_' . date('Y-m-d_H-i-s') . '.sql';
$command = "mysqldump --opt -h $servername -u $username -p$password $dbname > $backup_file";
system($command, $output);

if ($output == 0) {
    echo "Backup realizado com sucesso.";
} else {
    echo "Erro ao realizar backup.";
}
?>
