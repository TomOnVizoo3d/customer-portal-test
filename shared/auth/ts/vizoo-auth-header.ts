import { randomInt, createHash, Hash } from "crypto";
import SALTS_RAW from "../salts.txt";

const SALTS: readonly string[] = SALTS_RAW.split(/\r\n|\r|\n/g).filter((line: string) => !line.startsWith(";"));

function getRandomInteger(min: number, max: number): Promise<number> {
    return new Promise((resolve, reject) => {
        randomInt(min, max, (error: Error | null, value: number) => {
            if (error) {
                reject(error);
            }
            resolve(value);
        });
    });
}

export async function generateAuthenticationHeader(body: string): Promise<string> {
    const index0 = await getRandomInteger(0, SALTS.length);
    const index1 = await getRandomInteger(0, SALTS.length);

    const bodyHash: Hash = createHash("sha512");
    bodyHash.update(body, "utf-8");

    const authHash: Hash = createHash("sha512");
    authHash.update(`${SALTS[index0]}${bodyHash.digest("hex")}${SALTS[index1]}`, "hex");

    return `${index0} ${index1} ${authHash.digest("hex")}`;
}

export async function validateBody(xVizooAuthHeader: string, body: string): Promise<boolean> {
    const rawSegments = xVizooAuthHeader.split(" ", 3);

    if (rawSegments.length !== 3) {
        return false;
    }

    const index0: number = Number.parseInt(rawSegments[0], 10);
    const index1: number = Number.parseInt(rawSegments[1], 10);
    const requestHash: string = rawSegments[2];

    const sha512Regex: RegExp = /^[a-f0-9]{128}$/i;
    if (Number.isNaN(index0) || Number.isNaN(index1) || !sha512Regex.test(requestHash)) {
        return false;
    }

    const bodyHash: Hash = createHash("sha512");
    bodyHash.update(body, "utf-8");

    const authHash: Hash = createHash("sha512");
    authHash.update(`${SALTS[index0]}${bodyHash.digest("hex")}${SALTS[index1]}`, "hex");

    return authHash.digest("hex") === requestHash;
}
