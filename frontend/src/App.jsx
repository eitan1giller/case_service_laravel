import React, { useState } from "react";

export default function App() {
  const [tracking, setTracking] = useState("");
  const [status, setStatus] = useState(null);

  async function check() {
    if (!tracking) return;
    const res = await fetch(`/api/cases/${tracking}`);
    const body = await res.json();
    setStatus(body);
  }

  return (
    <div style={{ padding: 20 }}>
      <h2>Case Status</h2>
      <input
        value={tracking}
        onChange={(e) => setTracking(e.target.value)}
        placeholder="tracking id"
      />
      <button onClick={check}>Check</button>
      <pre>{status ? JSON.stringify(status, null, 2) : "no data"}</pre>
    </div>
  );
}
