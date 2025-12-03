<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Condiciones - Hao Mei Lai</title>
    <link rel="stylesheet" href="css/stylelogin.css">
    <style>
        .terminos-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .terminos-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #d32f2f;
            padding-bottom: 20px;
        }
        .terminos-header h1 {
            color: #d32f2f;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .terminos-header p {
            color: #666;
            font-size: 0.9em;
        }
        .terminos-section {
            margin-bottom: 25px;
        }
        .terminos-section h2 {
            color: #d32f2f;
            font-size: 1.4em;
            margin-bottom: 10px;
            border-left: 4px solid #d32f2f;
            padding-left: 15px;
        }
        .terminos-section h3 {
            color: #333;
            font-size: 1.1em;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        .terminos-section p, .terminos-section ul {
            color: #555;
            line-height: 1.8;
            text-align: justify;
        }
        .terminos-section ul {
            padding-left: 25px;
        }
        .terminos-section li {
            margin-bottom: 8px;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
        .btn-cerrar {
            display: block;
            width: 200px;
            margin: 30px auto;
            padding: 12px;
            background-color: #d32f2f;
            color: white;
            text-align: center;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none;
        }
        .btn-cerrar:hover {
            background-color: #b71c1c;
        }
    </style>
</head>
<body style="background: url('imagenes/fondo comida.jpg') no-repeat center center fixed; background-size: cover;">
    <div class="terminos-container">
        <div class="terminos-header">
            <h1>TÉRMINOS Y CONDICIONES DE USO</h1>
            <p><strong>Hao Mei Lai - Restaurante de Comida China</strong></p>
            <p>Última actualización: <?php echo date('d/m/Y'); ?></p>
        </div>

        <div class="terminos-section">
            <h2>1. ACEPTACIÓN DE TÉRMINOS</h2>
            <p>Al registrarse y utilizar los servicios de Hao Mei Lai, usted acepta expresamente estos Términos y Condiciones, así como nuestro Aviso de Privacidad, conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP) y demás normativa aplicable en los Estados Unidos Mexicanos.</p>
        </div>

        <div class="terminos-section">
            <h2>2. IDENTIFICACIÓN DEL RESPONSABLE</h2>
            <p><strong>Denominación:</strong> Hao Mei Lai</p>
            <p><strong>Giro:</strong> Restaurante de Comida China</p>
            <p><strong>Domicilio:</strong> [Dirección completa en Morelia, Michoacán, México]</p>
            <p>El presente sitio web y sistema de pedidos está regulado por las leyes mexicanas, específicamente la Ley Federal de Protección al Consumidor y el Código Civil Federal.</p>
        </div>

        <div class="terminos-section">
            <h2>3. OBJETO Y SERVICIOS</h2>
            <p>Hao Mei Lai ofrece a través de su plataforma digital:</p>
            <ul>
                <li>Consulta de menú y precios</li>
                <li>Realización de pedidos en línea</li>
                <li>Servicio de entrega a domicilio</li>
                <li>Gestión de cuenta de usuario</li>
                <li>Historial de pedidos</li>
                <li>Programas de lealtad y promociones</li>
            </ul>
        </div>

        <div class="terminos-section">
            <h2>4. REGISTRO Y CUENTA DE USUARIO</h2>
            <h3>4.1 Requisitos</h3>
            <ul>
                <li>Ser mayor de 18 años o contar con autorización de un tutor legal</li>
                <li>Proporcionar información veraz, exacta y actualizada</li>
                <li>Contar con correo electrónico válido</li>
                <li>Crear una contraseña segura</li>
            </ul>
            
            <h3>4.2 Responsabilidades del Usuario</h3>
            <ul>
                <li>Mantener la confidencialidad de su contraseña</li>
                <li>Notificar inmediatamente cualquier uso no autorizado de su cuenta</li>
                <li>Actualizar sus datos personales cuando sea necesario</li>
                <li>No compartir su cuenta con terceros</li>
            </ul>

            <h3>4.3 Opción de Invitado</h3>
            <p>Los usuarios pueden acceder como "Invitado" con funcionalidad limitada, sin necesidad de crear una cuenta permanente.</p>
        </div>

        <div class="terminos-section">
            <h2>5. PROTECCIÓN DE DATOS PERSONALES</h2>
            <p>Conforme a la LFPDPPP, Hao Mei Lai se compromete a:</p>
            <ul>
                <li>Recabar únicamente los datos necesarios para la prestación del servicio</li>
                <li>Proteger sus datos mediante medidas de seguridad físicas, técnicas y administrativas</li>
                <li>No transferir sus datos a terceros sin su consentimiento expreso, salvo excepciones legales</li>
                <li>Permitir el ejercicio de sus derechos ARCO (Acceso, Rectificación, Cancelación y Oposición)</li>
            </ul>
            
            <div class="highlight">
                <strong>Datos Recopilados:</strong> Nombre, correo electrónico, dirección de entrega, teléfono, historial de pedidos y preferencias alimentarias.
            </div>
        </div>

        <div class="terminos-section">
            <h2>6. PEDIDOS Y PAGOS</h2>
            <h3>6.1 Proceso de Pedido</h3>
            <ul>
                <li>Los pedidos están sujetos a disponibilidad de productos</li>
                <li>Los precios mostrados incluyen IVA según la legislación mexicana</li>
                <li>Hao Mei Lai se reserva el derecho de rechazar pedidos en casos justificados</li>
                <li>Se enviará confirmación de pedido al correo electrónico registrado</li>
            </ul>

            <h3>6.2 Formas de Pago</h3>
            <ul>
                <li>Efectivo contra entrega</li>
                <li>Tarjeta de crédito/débito (procesamiento seguro)</li>
                <li>Transferencia bancaria</li>
                <li>Otros métodos autorizados</li>
            </ul>

            <h3>6.3 Facturación</h3>
            <p>Conforme al SAT (Servicio de Administración Tributaria), los usuarios podrán solicitar factura electrónica (CFDI) dentro de las 72 horas posteriores a la compra, proporcionando su RFC y uso de CFDI.</p>
        </div>

        <div class="terminos-section">
            <h2>7. ENTREGAS</h2>
            <ul>
                <li>Los tiempos de entrega son estimados y pueden variar</li>
                <li>La zona de entrega está limitada a [especificar zona de cobertura]</li>
                <li>Se aplican cargos por servicio de entrega según distancia</li>
                <li>El cliente debe verificar el pedido al momento de la entrega</li>
            </ul>
        </div>

        <div class="terminos-section">
            <h2>8. CANCELACIONES Y DEVOLUCIONES</h2>
            <h3>8.1 Derecho de Retracto</h3>
            <p>Conforme a la Ley Federal de Protección al Consumidor (Art. 56 y 92):</p>
            <ul>
                <li>Cancelación gratuita antes de que el pedido entre en preparación</li>
                <li>Devolución del 100% en caso de productos defectuosos o errores en el pedido</li>
                <li>Tiempo máximo de respuesta: 5 días hábiles</li>
            </ul>

            <h3>8.2 Reclamos</h3>
            <p>Para cualquier inconformidad, el cliente puede contactar a través de:</p>
            <ul>
                <li>Correo electrónico: [email de soporte]</li>
                <li>Teléfono: [número de contacto]</li>
                <li>PROFECO: www.profeco.gob.mx (Teléfono del Consumidor: 5568 8722)</li>
            </ul>
        </div>

        <div class="terminos-section">
            <h2>9. PROPIEDAD INTELECTUAL</h2>
            <p>Todos los contenidos del sitio (textos, imágenes, logotipos, diseños) son propiedad de Hao Mei Lai o sus licenciantes, protegidos por la Ley Federal del Derecho de Autor. Queda prohibida su reproducción sin autorización expresa.</p>
        </div>

        <div class="terminos-section">
            <h2>10. CONDUCTA DEL USUARIO</h2>
            <p>El usuario se compromete a NO:</p>
            <ul>
                <li>Utilizar el servicio para fines ilícitos</li>
                <li>Proporcionar información falsa o fraudulenta</li>
                <li>Realizar pedidos falsos o con intención de fraude</li>
                <li>Acosar, amenazar o agredir al personal de Hao Mei Lai</li>
                <li>Vulnerar la seguridad del sistema</li>
            </ul>
        </div>

        <div class="terminos-section">
            <h2>11. RESPONSABILIDADES Y LIMITACIONES</h2>
            <ul>
                <li>Hao Mei Lai no se responsabiliza por daños derivados del uso indebido del servicio</li>
                <li>No garantizamos disponibilidad ininterrumpida del sitio web</li>
                <li>No nos responsabilizamos por alergias no informadas previamente</li>
                <li>La responsabilidad máxima se limita al monto pagado por el pedido</li>
            </ul>
        </div>

        <div class="terminos-section">
            <h2>12. MODIFICACIONES</h2>
            <p>Hao Mei Lai se reserva el derecho de modificar estos términos en cualquier momento. Los cambios serán notificados a través del sitio web y/o correo electrónico, entrando en vigor a partir de su publicación.</p>
        </div>

        <div class="terminos-section">
            <h2>13. JURISDICCIÓN Y LEY APLICABLE</h2>
            <p>Estos términos se rigen por las leyes de los Estados Unidos Mexicanos. Para cualquier controversia, las partes se someten a la jurisdicción de los tribunales de Morelia, Michoacán, renunciando a cualquier otro fuero que pudiera corresponderles.</p>
        </div>

        <div class="terminos-section">
            <h2>14. CONTACTO</h2>
            <p>Para dudas, aclaraciones o ejercicio de derechos ARCO:</p>
            <p><strong>Email:</strong> contacto@haomeilai.com</p>
            <p><strong>Teléfono:</strong> [Número de contacto]</p>
            <p><strong>Horario de atención:</strong> Lunes a Domingo de 10:00 a 22:00 hrs</p>
        </div>

        <div class="highlight">
            <strong>IMPORTANTE:</strong> Al registrarse, usted declara haber leído, entendido y aceptado todos estos Términos y Condiciones, así como nuestro Aviso de Privacidad.
        </div>

        <button class="btn-cerrar" onclick="window.close()">Cerrar Ventana</button>
    </div>
</body>
</html>