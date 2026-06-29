# Auth

This repo contains the salts for our auth-scheme, as well as ready-to-use implementations in several languages for the projects where the auth-scheme is used.

## Computation of the authentication digest

> [!CAUTION]
> This scheme is not cryptographically safe and just intended to serve as hindrance, not as security-measure!

The scheme consists of 4 parts:
- A cryptographically safe hash-function H (usually SHA512 for web and SHA256 for files)
- 2 indices I0 and I1 (either transmitted or chosen via cryptographically safe RNG)
- The payload data D
- The salts S from salts.txt

Our authentication is computed by:
    H(S[I0] @ H(D) @ S[I1])
    with @ being the binary concatenation

## Format of salts.txt

> [!CAUTION]
> Lines may be freely added to the file at the bottom but **never** (!) removed or reordered
- Lines starting with ; are comments and to be ignored
- All other lines are the hexadecimal representation of a hash-salt
