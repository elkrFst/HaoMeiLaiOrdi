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
    <title>Aviso de Privacidad - Hao Mei Lai</title>
    <link rel="stylesheet" href="css/stylelogin.css">
    <style>
        .privacidad-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .privacidad-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #d32f2f;
            padding-bottom: 20px;
        }
        .privacidad-header h1 {
            color: #d32f2f;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .privacidad-header p {
            color: #666;
            font-size: 0.9em;
        }
        .privacidad-section {
            margin-bottom: 25px;
        }
        .privacidad-section h2 {
            color: #d32f2f;
            font-size: 1.4em;
            margin-bottom: 10px;
            border-left: 4px solid #d32f2f;
            padding-left: 15px;
        }
        .privacidad-section h3 {
            color: #333;
            font-size: 1.1em;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        .privacidad-section p, .privacidad-section ul {
            color: #555;
            line-height: 1.8;
            text-align: justify;
        }
        .privacidad-section ul {
            padding-left: 25px;
        }
        .privacidad-section li {
            margin-bottom: 8px;
        }
        .highlight {
            background-color: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196f3;
            margin: 20px 0;
        }
        .alert-box {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
        .success-box {
            background-color: #d4edda;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
        }
        .tabla-datos {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .tabla-datos th, .tabla-datos td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .tabla-datos th {
            background-color: #d32f2f;
            color: white;
        }
        .tabla-datos tr:nth-child(even) {
            background-color: #f9f9f9;
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
        .contacto-box {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .contacto-box h3 {
            color: #d32f2f;
            margin-bottom: 10px;
        }
    </style>
</head>
<body style="background: url('imagenes/fondo comida.jpg') no-repeat center center fixed; background-size: cover;">
    <div class="privacidad-container">
        <div class="privacidad-header">
            <h1>AVISO DE PRIVACIDAD</h1>
            <p><strong>Hao Mei Lai - Restaurante de Comida China</strong></p>
            <p>Última actualización: <?php echo date('d/m/Y'); ?></p>
        </div>

        <div class="alert-box">
            <strong>⚖️ AVISO CONFORME A LA LEY:</strong> El presente Aviso de Privacidad se emite en cumplimiento de la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP), su Reglamento y los Lineamientos del Aviso de Privacidad publicados por el Instituto Nacional de Transparencia, Acceso a la Información y Protección de Datos Personales (INAI).
        </div>

        <div class="privacidad-section">
            <h2>1. IDENTIDAD Y DOMICILIO DEL RESPONSABLE</h2>
            <p><strong>Denominación o Razón Social:</strong> Hao Mei Lai</p>
            <p><strong>Nombre Comercial:</strong> Hao Mei Lai - Restaurante de Comida China</p>
            <p><strong>Domicilio:</strong> [Calle, Número, Colonia, C.P., Morelia, Michoacán, México]</p>
            <p><strong>Teléfono:</strong> [Número de contacto]</p>
            <p><strong>Correo Electrónico:</strong> privacidad@haomeilai.com</p>
            <p><strong>Sitio Web:</strong> www.haomeilai.com</p>
            
            <p>Hao Mei Lai, en adelante "EL RESPONSABLE", es responsable del tratamiento de sus datos personales y se compromete a proteger su privacidad y garantizar su derecho a la autodeterminación informativa.</p>
        </div>

        <div class="privacidad-section">
            <h2>2. DATOS PERSONALES QUE RECABAMOS</h2>
            <p>Para las finalidades señaladas en este aviso de privacidad, EL RESPONSABLE puede recabar y tratar las siguientes categorías de datos personales:</p>
            
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Datos Recopilados</th>
                        <th>Finalidad Principal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Datos de Identificación</strong></td>
                        <td>Nombre completo, correo electrónico, número telefónico, fotografía (opcional)</td>
                        <td>Identificación del usuario y comunicación</td>
                    </tr>
                    <tr>
                        <td><strong>Datos de Contacto</strong></td>
                        <td>Dirección de entrega, código postal, referencias del domicilio</td>
                        <td>Entrega de pedidos a domicilio</td>
                    </tr>
                    <tr>
                        <td><strong>Datos de Facturación</strong></td>
                        <td>RFC, régimen fiscal, uso de CFDI, razón social, domicilio fiscal</td>
                        <td>Emisión de facturas electrónicas (CFDI)</td>
                    </tr>
                    <tr>
                        <td><strong>Datos Transaccionales</strong></td>
                        <td>Historial de pedidos, preferencias alimentarias, métodos de pago</td>
                        <td>Procesamiento de pedidos y mejora del servicio</td>
                    </tr>
                    <tr>
                        <td><strong>Datos Sensibles</strong></td>
                        <td>Alergias alimentarias, restricciones dietéticas</td>
                        <td>Garantizar seguridad alimentaria</td>
                    </tr>
                    <tr>
                        <td><strong>Datos Técnicos</strong></td>
                        <td>Dirección IP, cookies, tipo de navegador, sistema operativo</td>
                        <td>Seguridad y funcionamiento del sitio web</td>
                    </tr>
                </tbody>
            </table>

            <div class="alert-box">
                <strong>⚠️ DATOS SENSIBLES:</strong> Le informamos que se recaban datos personales sensibles (alergias e intolerancias alimentarias) ÚNICAMENTE con su consentimiento expreso y para garantizar su seguridad. El tratamiento de estos datos es OPCIONAL y puede negarse sin afectar el servicio básico.
            </div>
        </div>

        <div class="privacidad-section">
            <h2>3. FINALIDADES DEL TRATAMIENTO</h2>
            
            <h3>3.1 Finalidades Primarias (necesarias para el servicio):</h3>
            <p>Para las cuales NO se requiere su consentimiento, ya que son necesarias para la relación jurídica entre usted y EL RESPONSABLE:</p>
            <ul>
                <li>✅ Identificación y autenticación del usuario</li>
                <li>✅ Procesamiento y gestión de pedidos</li>
                <li>✅ Entrega de productos a domicilio</li>
                <li>✅ Procesamiento de pagos y cobros</li>
                <li>✅ Emisión de comprobantes fiscales (facturas CFDI)</li>
                <li>✅ Atención de quejas, dudas y aclaraciones</li>
                <li>✅ Cumplimiento de obligaciones legales y fiscales</li>
                <li>✅ Seguridad de la plataforma y prevención de fraudes</li>
                <li>✅ Gestión de devoluciones y cancelaciones</li>
            </ul>

            <h3>3.2 Finalidades Secundarias (requieren su consentimiento):</h3>
            <p>Para las cuales SÍ se requiere su consentimiento expreso:</p>
            <ul>
                <li>📧 Envío de promociones, ofertas y descuentos personalizados</li>
                <li>📧 Notificaciones sobre nuevos productos y menús especiales</li>
                <li>📧 Invitaciones a eventos y celebraciones</li>
                <li>📧 Programas de lealtad y recompensas</li>
                <li>📧 Encuestas de satisfacción y estudios de mercado</li>
                <li>📧 Comunicaciones de marketing y publicidad</li>
            </ul>

            <div class="success-box">
                <strong>✔️ USTED PUEDE NEGARSE:</strong> Si no desea que sus datos personales se traten para finalidades secundarias, puede manifestarlo enviando un correo a: privacidad@haomeilai.com con el asunto "NEGATIVA FINALIDADES SECUNDARIAS". La negativa no será motivo para que le neguemos los servicios solicitados.
            </div>
        </div>

        <div class="privacidad-section">
            <h2>4. MEDIOS PARA EJERCER DERECHOS ARCO</h2>
            <p>Usted tiene derecho a conocer qué datos personales tenemos de usted, para qué los utilizamos y las condiciones del uso que les damos (Acceso). Asimismo, es su derecho solicitar la corrección de su información personal en caso de que esté desactualizada, sea inexacta o incompleta (Rectificación); que la eliminemos de nuestros registros o bases de datos cuando considere que la misma no está siendo utilizada conforme a los principios, deberes y obligaciones previstas en la normativa (Cancelación); así como oponerse al uso de sus datos personales para fines específicos (Oposición). Estos derechos se conocen como derechos ARCO.</p>

            <h3>4.1 ¿Cómo ejercer sus derechos ARCO?</h3>
            <p>Para ejercer cualquiera de los derechos ARCO, debe presentar una solicitud mediante:</p>
            <ul>
                <li><strong>Correo Electrónico:</strong> privacidad@haomeilai.com</li>
                <li><strong>Por escrito en:</strong> [Domicilio completo]</li>
                <li><strong>Formato disponible en:</strong> www.haomeilai.com/derechos-arco</li>
            </ul>

            <h3>4.2 Requisitos de la Solicitud:</h3>
            <ul>
                <li>Nombre completo del titular y correo electrónico para recibir respuesta</li>
                <li>Descripción clara y precisa de los datos personales respecto de los cuales busca ejercer alguno de los derechos ARCO</li>
                <li>Cualquier documento que facilite la localización de los datos personales</li>
                <li>Identificación oficial vigente (INE/IFE, pasaporte, cédula profesional)</li>
            </ul>

            <div class="highlight">
                <strong>⏱️ PLAZO DE RESPUESTA:</strong> EL RESPONSABLE dará respuesta a su solicitud en un plazo máximo de 20 (veinte) días hábiles contados desde la fecha en que se recibió la solicitud de acceso, rectificación, cancelación u oposición. La respuesta será comunicada al correo electrónico proporcionado.
            </div>
        </div>

        <div class="privacidad-section">
            <h2>5. REVOCACIÓN DEL CONSENTIMIENTO</h2>
            <p>Usted puede revocar su consentimiento para el tratamiento de sus datos personales en cualquier momento, mediante el mismo procedimiento establecido para ejercer los derechos ARCO.</p>
            
            <p>Es importante que tenga en cuenta que:</p>
            <ul>
                <li>No en todos los casos podremos atender su solicitud o concluir el uso de forma inmediata</li>
                <li>Es posible que por alguna obligación legal o contractual requiramos seguir tratando sus datos personales</li>
                <li>La revocación de datos necesarios para finalidades primarias puede resultar en la imposibilidad de continuar prestando el servicio</li>
            </ul>
        </div>

        <div class="privacidad-section">
            <h2>6. TRANSFERENCIA DE DATOS PERSONALES</h2>
            <p>Le informamos que sus datos personales pueden ser compartidos dentro y fuera del país con las siguientes personas, empresas, organizaciones o autoridades distintas a nosotros, para los siguientes fines:</p>

            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Destinatario</th>
                        <th>Finalidad</th>
                        <th>Requiere Consentimiento</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Servicios de Mensajería y Paquetería</td>
                        <td>Entrega de pedidos a domicilio</td>
                        <td>NO (necesaria para el servicio)</td>
                    </tr>
                    <tr>
                        <td>Instituciones Bancarias y Procesadores de Pago</td>
                        <td>Procesamiento de pagos con tarjeta</td>
                        <td>NO (necesaria para el servicio)</td>
                    </tr>
                    <tr>
                        <td>Servicio de Administración Tributaria (SAT)</td>
                        <td>Cumplimiento de obligaciones fiscales</td>
                        <td>NO (obligación legal)</td>
                    </tr>
                    <tr>
                        <td>Proveedores de Servicios Tecnológicos</td>
                        <td>Hosting, almacenamiento en nube, mantenimiento</td>
                        <td>NO (necesaria para el servicio)</td>
                    </tr>
                    <tr>
                        <td>Autoridades Competentes</td>
                        <td>Cumplimiento de requerimientos legales</td>
                        <td>NO (obligación legal)</td>
                    </tr>
                    <tr>
                        <td>Agencias de Marketing</td>
                        <td>Campañas publicitarias personalizadas</td>
                        <td>SÍ (finalidad secundaria)</td>
                    </tr>
                </tbody>
            </table>

            <p>Le informamos que NO vendemos, rentamos ni compartimos sus datos personales con terceros para fines distintos a los aquí señalados.</p>
        </div>

        <div class="privacidad-section">
            <h2>7. USO DE COOKIES Y TECNOLOGÍAS DE RASTREO</h2>
            <p>Le informamos que en nuestra página de internet utilizamos cookies, web beacons y otras tecnologías de rastreo, a través de las cuales es posible monitorear su comportamiento como usuario de internet, así como brindarle un mejor servicio y experiencia al navegar en nuestra página.</p>

            <h3>7.1 Tipos de Cookies que Utilizamos:</h3>
            <ul>
                <li><strong>Cookies Esenciales:</strong> Necesarias para el funcionamiento del sitio (inicio de sesión, carrito de compras)</li>
                <li><strong>Cookies de Rendimiento:</strong> Nos ayudan a mejorar el funcionamiento del sitio</li>
                <li><strong>Cookies de Funcionalidad:</strong> Recuerdan sus preferencias (idioma, ubicación)</li>
                <li><strong>Cookies de Publicidad:</strong> Permiten mostrar publicidad relevante (requiere consentimiento)</li>
            </ul>

            <h3>7.2 Cómo Deshabilitar las Cookies:</h3>
            <p>Usted puede deshabilitar las cookies en su navegador siguiendo las instrucciones específicas de cada uno:</p>
            <ul>
                <li><strong>Google Chrome:</strong> Configuración > Privacidad y seguridad > Cookies</li>
                <li><strong>Firefox:</strong> Opciones > Privacidad y seguridad > Cookies</li>
                <li><strong>Safari:</strong> Preferencias > Privacidad > Cookies</li>
                <li><strong>Edge:</strong> Configuración > Cookies y permisos del sitio</li>
            </ul>

            <div class="alert-box">
                <strong>⚠️ IMPORTANTE:</strong> Deshabilitar las cookies puede afectar la funcionalidad del sitio y limitar su experiencia de usuario.
            </div>
        </div>

        <div class="privacidad-section">
            <h2>8. MEDIDAS DE SEGURIDAD</h2>
            <p>EL RESPONSABLE ha implementado medidas de seguridad administrativas, técnicas y físicas para proteger sus datos personales, las cuales incluyen:</p>
            <ul>
                <li>🔒 Cifrado SSL/TLS en todas las comunicaciones del sitio web</li>
                <li>🔒 Protección con contraseña encriptada mediante hash</li>
                <li>🔒 Servidores seguros con certificados de seguridad actualizados</li>
                <li>🔒 Firewalls y sistemas de detección de intrusiones</li>
                <li>🔒 Acceso restringido a datos personales solo a personal autorizado</li>
                <li>🔒 Respaldos periódicos de información</li>
                <li>🔒 Protocolos de respuesta ante incidentes de seguridad</li>
                <li>🔒 Capacitación continua al personal sobre protección de datos</li>
            </ul>
        </div>

        <div class="privacidad-section">
            <h2>9. CAMBIOS AL AVISO DE PRIVACIDAD</h2>
            <p>EL RESPONSABLE se reserva el derecho de efectuar en cualquier momento modificaciones o actualizaciones al presente aviso de privacidad, para la atención de novedades legislativas, políticas internas o nuevos requerimientos para la prestación u ofrecimiento de nuestros servicios o productos.</p>
            
            <p>Estas modificaciones estarán disponibles al público a través de los siguientes medios:</p>
            <ul>
                <li>📱 Sitio web: www.haomeilai.com/aviso-privacidad</li>
                <li>📱 Notificación por correo electrónico a usuarios registrados</li>
                <li>📱 Anuncios en el establecimiento físico</li>
            </ul>

            <div class="highlight">
                <strong>Fecha de última actualización:</strong> <?php echo date('d/m/Y'); ?>
            </div>
        </div>

        <div class="privacidad-section">
            <h2>10. PROCEDIMIENTO PARA QUEJAS ANTE EL INAI</h2>
            <p>Si usted considera que su derecho de protección de datos personales ha sido lesionado por alguna conducta de nuestros empleados o de nuestras actuaciones o respuestas, presume que en el tratamiento de sus datos personales existe alguna violación a las disposiciones previstas en la Ley Federal de Protección de Datos Personales en Posesión de los Particulares, podrá interponer la queja o denuncia correspondiente ante el INAI.</p>

            <div class="contacto-box">
                <h3>📞 Contacto INAI:</h3>
                <p><strong>Instituto Nacional de Transparencia, Acceso a la Información y Protección de Datos Personales</strong></p>
                <p><strong>Sitio web:</strong> www.inai.org.mx</p>
                <p><strong>Teléfono:</strong> 800 835 4324</p>
                <p><strong>Correo:</strong> info@inai.org.mx</p>
                <p><strong>Dirección:</strong> Insurgentes Sur 3211, Colonia Insurgentes Cuicuilco, Alcaldía Coyoacán, C.P. 04530, Ciudad de México</p>
            </div>
        </div>

        <div class="privacidad-section">
            <h2>11. CONSENTIMIENTO</h2>
            <div class="success-box">
                <p><strong>Al proporcionar sus datos personales por cualquier medio, incluyendo el registro en nuestro sitio web, la realización de pedidos, o el uso de nuestros servicios, usted consiente tácitamente el presente Aviso de Privacidad y acepta que EL RESPONSABLE trate sus datos personales conforme al mismo.</strong></p>
                
                <p><strong>Para finalidades secundarias (marketing, publicidad, promociones), se solicitará su consentimiento expreso mediante checkbox o confirmación por correo electrónico.</strong></p>
            </div>
        </div>

        <div class="contacto-box">
            <h3>📧 Contacto para Asuntos de Privacidad:</h3>
            <p><strong>Departamento de Protección de Datos Personales</strong></p>
            <p><strong>Email:</strong> privacidad@haomeilai.com</p>
            <p><strong>Teléfono:</strong> [Número de contacto]</p>
            <p><strong>Horario de atención:</strong> Lunes a Viernes de 9:00 a 18:00 hrs</p>
            <p><strong>Domicilio:</strong> [Dirección completa]</p>
        </div>

        <button class="btn-cerrar" onclick="window.close()">Cerrar Ventana</button>
    </div>
</body>
</html>