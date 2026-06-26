# AARM RC2 Roadmap

This project is now under RC2 refactor.

Rules:
1. No *_v2.php, *_final.php, *_fix.php
2. One file = one responsibility.
3. All brains return:
   vote, score, key, status, weight, final, reason.
4. Consensus processes a brain registry instead of hardcoded brains.
5. Signal Generator reads consensus only.
6. Dashboard is presentation only.

Phase 1:
- Refactor brain_consensus_engine_v3.php using processBrain().
- Preserve JSON output compatibility.

