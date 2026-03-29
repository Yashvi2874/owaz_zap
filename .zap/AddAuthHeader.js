// =============================================================
//  AddAuthHeader.js — ZAP HttpSender Script
//
//  DEMO STEP: Feature 4 — Scripting
//
//  HOW TO USE IN ZAP:
//    1. View → Show Tab → Scripts
//    2. Expand "HttpSender" in the Scripts tree
//    3. Right-click HttpSender → New Script
//    4. Name: AddAuthHeader
//    5. Language: JavaScript
//    6. Template: HttpSender default
//    7. Delete template content, paste this entire file
//    8. Click Save
//    9. Tick the checkbox next to AddAuthHeader to enable it
//   10. Browse any VulnShop page through ZAP
//   11. History tab → click latest request → Request tab
//       → Observe X-Demo-User and Authorization headers on EVERY request
//   12. Untick the script → make another request → headers are GONE
//   13. Re-tick → headers come back — toggle this live for the audience
//
//  WHAT THIS PROVES:
//    In real engagements, APIs issue JWT tokens that expire every
//    15 minutes. One HttpSender script can refresh and inject the
//    token automatically — your overnight scan runs uninterrupted.
// =============================================================

/**
 * sendingRequest — fires before every HTTP request ZAP sends.
 * Use this to inject headers, modify parameters, or log outgoing traffic.
 *
 * @param {HttpMessage} msg       — the full HTTP request/response pair
 * @param {int}         initiator — what triggered this request (spider, active scan, etc.)
 * @param {ScriptVars}  helper    — ZAP helper utilities
 */
function sendingRequest(msg, initiator, helper) {
    // ── Inject a custom user identity header ──────────────────
    // In real usage: extract the username from a session/token
    msg.getRequestHeader().setHeader("X-Demo-User", "john");

    // ── Inject a Bearer token ─────────────────────────────────
    // In real usage: call a token endpoint here, parse the JWT,
    // and inject the fresh token on every request automatically.
    msg.getRequestHeader().setHeader("Authorization", "Bearer demo-token-12345");

    // ── Log the outgoing request URL to the ZAP output tab ────
    print("[AddAuthHeader] Injecting headers → " + msg.getRequestHeader().getURI());
}

/**
 * responseReceived — fires after every HTTP response ZAP receives.
 * Use this to inspect responses, extract tokens, or flag anomalies.
 *
 * @param {HttpMessage} msg       — the full HTTP request/response pair
 * @param {int}         initiator — what triggered this request
 * @param {ScriptVars}  helper    — ZAP helper utilities
 */
function responseReceived(msg, initiator, helper) {
    var status = msg.getResponseHeader().getStatusCode();
    var url    = msg.getRequestHeader().getURI().toString();

    // Log every response: URL → HTTP status code
    print("[AddAuthHeader] Response: " + url + " → " + status);

    // ── Example: Token refresh logic ──────────────────────────
    // In a real scenario, detect a 401 Unauthorized response and
    // re-authenticate to get a fresh token before the next request.
    //
    // if (status === 401) {
    //     var freshToken = refreshToken();   // your custom function
    //     print("[AddAuthHeader] Token expired — refreshed.");
    // }
}
