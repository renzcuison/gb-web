<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inquiry Received — GreatBuy Originals</title>
</head>
<body style="margin:0;padding:32px 16px;background-color:#ffffff;color:#18181b;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <div style="max-width:560px;margin:0 auto;border:1px solid #e4e4e7;padding:32px;">
        <div style="font-size:11px;letter-spacing:0.3em;text-transform:uppercase;color:#a1a1aa;font-weight:700;">
            GreatBuy Originals
        </div>

        <h1 style="margin:14px 0 8px;font-size:20px;letter-spacing:0.05em;text-transform:uppercase;color:#18181b;">
            Inquiry #{{ $inquiry->id }} Received
        </h1>

        <p style="margin:0 0 24px;font-size:13px;line-height:1.6;color:#52525b;">
            Hi {{ $inquiry->customer_name }}, thank you for reaching out. We have logged your product inquiry and will confirm availability and dispatch details within one business day.
        </p>

        <table style="width:100%;border-collapse:collapse;border-top:1px solid #f4f4f5;border-bottom:1px solid #f4f4f5;margin-bottom:24px;font-size:12px;">
            <tr>
                <td style="padding:10px 0;color:#71717a;width:130px;">Piece</td>
                <td style="padding:10px 0;color:#18181b;font-weight:600;">{{ $inquiry->product?->name ?? 'Product' }}</td>
            </tr>
            @if($inquiry->variant)
            <tr>
                <td style="padding:10px 0;color:#71717a;border-top:1px solid #f4f4f5;">Option / SKU</td>
                <td style="padding:10px 0;color:#18181b;border-top:1px solid #f4f4f5;">
                    {{ $inquiry->variant->variant_label }} ({{ $inquiry->variant->sku }})
                </td>
            </tr>
            @endif
            <tr>
                <td style="padding:10px 0;color:#71717a;border-top:1px solid #f4f4f5;">Quantity</td>
                <td style="padding:10px 0;color:#18181b;border-top:1px solid #f4f4f5;">{{ $inquiry->quantity }}</td>
            </tr>
            <tr>
                <td style="padding:10px 0;color:#71717a;border-top:1px solid #f4f4f5;vertical-align:top;">Message</td>
                <td style="padding:10px 0;color:#3f3f46;border-top:1px solid #f4f4f5;white-space:pre-line;">{{ $inquiry->message }}</td>
            </tr>
        </table>

        <p style="margin:0;font-size:11px;color:#a1a1aa;">
            GreatBuy Originals · Manila, Philippines
        </p>
    </div>
</body>
</html>