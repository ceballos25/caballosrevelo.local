<div id="voucherCapture">
    <div style="max-width:560px;margin:22px auto;font-family:'Segoe UI',Arial,sans-serif;background:#f9f9f9;padding:14px;border-radius:18px;border:1px solid #eee;">

        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="
            background:#ffffff;
            border-radius:16px;
            overflow:hidden;
            color:#333333;
            border-collapse:collapse;
            border:1px solid #00ff00;
            box-shadow:0 8px 32px rgba(0,255,0,0.08);
        ">

            <tr>
                <td style="padding:16px 20px;border-bottom:2px solid #00ff00;background:#ffffff;">
                    <table width="100%">
                        <tr>
                            <td align="left">
                                <img src="https://eldiadetusuerte.com/assets/images/logos/logo-blanco.jpg" width="120px">
                                <p style="margin:4px 0 0;font-size:10px;color:#666;text-transform:uppercase;letter-spacing:2px;">
                                </p>
                            </td>
                            <td align="right" style="vertical-align:middle;">
                                <a href="javascript:void(0)" onclick="shareVoucher(this)" title="Compartir"
                                   style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;background:#f0f0f0;border:1px solid #00e600;text-decoration:none;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00e600" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="18" cy="5" r="3"></circle>
                                        <circle cx="6" cy="12" r="3"></circle>
                                        <circle cx="18" cy="19" r="3"></circle>
                                        <line x1="8.6" y1="13.5" x2="15.4" y2="17.5"></line>
                                        <line x1="15.4" y1="6.5" x2="8.6" y2="10.5"></line>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td style="padding:22px 25px 10px;" align="left">
                    <p style="font-size:13px;margin:0;color:#555;">Hola,</p>
                    <strong style="color:#000;font-size:16px;display:block;margin-top:6px;">
                        {Nombre Cliente}
                    </strong>
                </td>
            </tr>

            <tr>
                <td style="padding:0 25px 20px;">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:12px;border-top:1px solid #eee;padding-top:15px;">
                        
                        <tr>
                            <td style="padding:7px 0;color:#555;"><strong>ID:</strong></td>
                            <td align="right" style="padding:7px 0;color:#000;">#{ID}</td>
                        </tr>

                        <tr>
                            <td style="padding:7px 0;color:#555;"><strong>Fecha Compra:</strong></td>
                            <td align="right" style="padding:7px 0;color:#000;">{Fecha}</td>
                        </tr>

                        <tr>
                            <td style="padding:7px 0;color:#555;"><strong>Cant. Números:</strong></td>
                            <td align="right" style="padding:7px 0;color:#555;font-weight:bold;font-size:13px;">{Cantidad}</td>
                        </tr>
                        <tr>
                            <td style="padding:7px 0;color:#555;"><strong>Evento:</strong></td>
                            <td align="right" style="padding:7px 0;color:#555;">🛵⚡️ EVENTO FLASH, NMAX V3 2027 PARA EL PREMIO MAYOR Y PARA EL NÚMERO INVERTID0 2 PALIT0S. 💶 JUEG4 ESTE 30 DE MAYO CON LA DE BOYACÁ</td>
                        </tr>                        

                        <tr>
                            <td colspan="2" style="padding:12px 0 6px;color:#555;">
                                <strong>Código de Seguridad:</strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding:12px;background:#ffffff;border-radius:10px;color:#00c800;font-family:monospace;font-size:15px;text-align:center;border:1px solid #00c800;font-weight:bold;letter-spacing:2px;">
                                {Codigo}
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>

            <tr>
                <td align="center" style="padding:0 20px 30px;">
                    <div style="background:#f9f9f9;border:2px dashed #000000;border-radius:14px;padding:22px;">
                        <p style="margin:0 0 15px;font-size:11px;color:#000;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">
                            Tus Números
                        </p>
                        <div style="font-size:14px;color:#000;">
                            {NumerosHTML}
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td align="center" style="padding:25px 15px;background:#f4f4f4;border-top:1px solid #eee;">
                    
                    <p style="margin:0;color:#666;font-size:11px;text-transform:uppercase;letter-spacing:1px;">
                        Total Pagado
                    </p>

                    <h2 style="margin:8px 0 25px;color:#00c800;font-size:22px;font-weight:700;">
                        {Total}
                    </h2>

                    <a href="{GrupoUrl}"
                        style="background:linear-gradient(135deg,#00d900,#00b300);color:#ffffff;padding:11px 28px;border-radius:999px;text-decoration:none;font-weight:bold;font-size:12px;text-transform:uppercase;display:inline-block;letter-spacing:1px;box-shadow:0 4px 12px rgba(0,200,0,0.2);">
                        Ir al grupo
                    </a>

                    <p style="margin:20px 0 0;font-size:10px;color:#777;">
                        Hemos enviado una copia a tu correo electrónico.
                    </p>

                </td>
            </tr>

        </table>
    </div>
</div>