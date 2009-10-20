#!/usr/bin/env perl

use strict;
use File::Copy;
use DBI;

$ARGV[1] or &bail;

chdir $ARGV[0] or die "Directory $ARGV[0] doesn't exist";

$ARGV[0] =~ s|/$||g;

our $baseDir = $ARGV[0];
our $siteUrl = $ARGV[1];

-e $baseDir . '/db/db.sql' or die "File $baseDir/db/db.sql doesn't exist!";

our $sqlFile = '';

if ($ARGV[2] eq '--dumpdb') {
    #fazemos o dump
    $sqlFile = &dumpDb;
} else {
    $sqlFile = $baseDir . '/db/db.sql';
}

our ($oldSiteUrl, $oldUploadPath) = &getOldData;
&parseSqlFile;

sub parseSqlFile {
    my $tmpSqlFile = $baseDir . '/db/tmpdb.sql';
    my $uploadPath = $baseDir . '/src/wp-content/uploads';
    
    open ARQ, "$sqlFile" or die "File $sqlFile doesn't exist";
    open TMPARQ, ">$tmpSqlFile" or die "Could not create file $tmpSqlFile";
    
    while (my $line = <ARQ>) {
        $line =~ s|$oldSiteUrl|$siteUrl|g;
        $line =~ s|$oldUploadPath|$uploadPath|g;
        print TMPARQ $line;
    }
    
    close TMPARQ;
    close ARQ;
    
    move($sqlFile, $sqlFile . '.back');
    move($tmpSqlFile, $sqlFile);
}

sub getOldData {
    open ARQ, "$sqlFile" or die "File $sqlFile doesn't exist";
    while (my $line = <ARQ>) {
        if ($line =~ m/siteurl','(.*?)',/) {
            our $oldSiteUrl = $1;
        }
        if ($line =~ m/upload_path','(.*?)',/) {
            our $oldUploadPath = $1;
        }
    }
    close ARQ;
    return ($oldSiteUrl, $oldUploadPath);
}

sub dumpDb {
    #ler o wp-config e pegar: host, user, pass, dbname
    my %connData = ('DB_NAME','','DB_USER','','DB_PASSWORD','','DB_HOST','','DB_CHARSET','');
    my $wp_config = $baseDir . '/src/wp-config.php';
    my $tmpFile = '/tmp/tmp_db.sql';
    open WPCONFIG, $wp_config or die "File $wp_config doesn't exist";
    while (my $line = <WPCONFIG>) {
        foreach my $key (keys %connData) {
            if ($line =~ m/define\('$key', '(.+?)'\);/) {
                $connData{$key} = $1;
            }
        }
        
    }

    #dump
    system("mysqldump -h $connData{'DB_HOST'} -u $connData{'DB_USER'} -p$connData{'DB_PASSWORD'} --default-character-set=$connData{'DB_CHARSET'} $connData{'DB_NAME'} > $tmpFile");

    #retorna o nome do arquivo
    return $tmpFile;
}

sub syncDatabase {

}

sub syncWpContentDir {

}

sub bail {
    print qq{
\tUsage: ./migra_wordpress.pl pathToSiteSVNRepo newSiteUrl [--dumpdb]
\tExample: ./migra_wordpress.pl /home/rodrigo/devel/eletro/trunk http://localhost/eletro

\tpathToSiteSVNRepo: Caminho para o repositório svn. Deve apontar para a raíz de uma tag ou do trunk, onde existam as pastas src e db
\tnewSiteUrl: A nova URL para onde você quer migrar o site
\t--dumpdb: Se você não passar esta opção, o script usará o arquivo db/db.sql. Se você usar, ele fará o dump do banco de dados, usando a conexão descrita em src/wp-config.php

};
    exit;
}

