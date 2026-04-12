#!/bin/bash
# ─────────────────────────────────────────────
#  SysTemp Launcher — matsdj09@PC-zorin
#  Run this once. It starts the proxy and opens
#  your temperature dashboard in the browser.
# ─────────────────────────────────────────────

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
HTML_FILE="$SCRIPT_DIR/temps.html"
PORT=27182

# Kill any old proxy on that port
fuser -k $PORT/tcp 2>/dev/null

echo "🌡  Starting SysTemp proxy on port $PORT..."

# Start the proxy in the background
python3 - <<'PYEOF' &
import http.server, subprocess

class SensorHandler(http.server.BaseHTTPRequestHandler):
    def do_GET(self):
        try:
            out = subprocess.check_output(['sensors'], stderr=subprocess.DEVNULL).decode()
        except Exception as e:
            out = f"ERROR: {e}"
        # Append NVIDIA GPU temp via nvidia-smi if available
        try:
            smi = subprocess.check_output(
                ['nvidia-smi', '--query-gpu=temperature.gpu', '--format=csv,noheader'],
                stderr=subprocess.DEVNULL).decode().strip()
            nvidia_temp = float(smi.split('\n')[0].strip())
            out += f"\nnvidia-smi-virtual-0\nAdapter: Virtual device\nGPU Core:     +{nvidia_temp:.1f}\xb0C  (high = +83.0\xb0C)\n               (crit = +90.0\xb0C)\n"
        except Exception:
            pass  # nvidia-smi not available or no NVIDIA GPU
        self.send_response(200)
        self.send_header('Content-Type', 'text/plain; charset=utf-8')
        self.send_header('Access-Control-Allow-Origin', '*')
        self.end_headers()
        self.wfile.write(out.encode())

    def log_message(self, *args):
        pass  # keep terminal clean

print("SysTemp proxy running on http://localhost:27182")
http.server.HTTPServer(('localhost', 27182), SensorHandler).serve_forever()
PYEOF

PROXY_PID=$!
sleep 0.8  # give it a moment to bind

echo "🌐 Opening dashboard..."
xdg-open "$HTML_FILE" 2>/dev/null || firefox "$HTML_FILE" 2>/dev/null || chromium "$HTML_FILE"

echo ""
echo "✅ SysTemp is running!"
echo "   Dashboard : $HTML_FILE"
echo "   Proxy PID : $PROXY_PID  (port $PORT)"
echo "   Press Ctrl+C to stop the proxy."
echo ""

# Keep script alive so proxy stays up; clean up on exit
trap "echo '🛑 Stopping proxy...'; kill $PROXY_PID 2>/dev/null; fuser -k $PORT/tcp 2>/dev/null" EXIT
wait $PROXY_PID
