<?php
authenticate_admin();
DB::schema()->dropIfExists('conta_azul_tratativa_receber');
DB::schema()->dropIfExists('conta_azul_tratativa_pagar');
